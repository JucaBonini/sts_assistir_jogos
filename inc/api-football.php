<?php
/**
 * Integração da API Football-Data.org no tema Assistir Jogos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Configurações padrão de Ligas (football-data.org)
define( 'FOOTBALL_DATA_DEFAULT_LEAGUES', array(
    'BSA' => 'Brasileirão Série A',
    'CL'  => 'Champions League',
    'PL'  => 'Premier League',
    'PD'  => 'La Liga'
));

// Registrar página de menu no WordPress Admin
function api_football_adicionar_menu() {
    add_submenu_page(
        'edit.php?post_type=jogo',
        'Importador Football-Data',
        'Importador API',
        'manage_options',
        'api-football-import',
        'api_football_renderizar_pagina'
    );
}
add_action( 'admin_menu', 'api_football_adicionar_menu' );

// Inicializar opções no admin
function api_football_registrar_settings() {
    register_setting( 'api_football_options_group', 'api_football_key' );
    register_setting( 'api_football_options_group', 'api_football_leagues' );
}
add_action( 'admin_init', 'api_football_registrar_settings' );

// Agendar sincronização automática diária
function api_football_agendar_cron() {
    if ( ! wp_next_scheduled( 'api_football_daily_sync_event' ) ) {
        wp_schedule_event( time(), 'daily', 'api_football_daily_sync_event' );
    }
}
add_action( 'wp', 'api_football_agendar_cron' );
add_action( 'api_football_daily_sync_event', 'api_football_sincronizar_jogos' );

/**
 * Função principal para buscar e salvar os jogos usando a API football-data.org
 */
function api_football_sincronizar_jogos( $data_sincronizacao = null, $leagues_to_sync = null ) {
    $api_key = get_option( 'api_football_key' );
    if ( empty( $api_key ) ) {
        return array( 'success' => false, 'message' => 'Token da API não configurado.' );
    }

    if ( ! $data_sincronizacao ) {
        // Pega data local de São Paulo
        $timezone = new DateTimeZone( 'America/Sao_Paulo' );
        $now = new DateTime( 'now', $timezone );
        $data_sincronizacao = $now->format( 'Y-m-d' );
    }

    if ( ! $leagues_to_sync ) {
        $leagues_to_sync = get_option( 'api_football_leagues', array_keys( FOOTBALL_DATA_DEFAULT_LEAGUES ) );
    }

    if ( empty( $leagues_to_sync ) ) {
        return array( 'success' => false, 'message' => 'Nenhuma liga selecionada para sincronização.' );
    }

    $imported_count = 0;
    $updated_count = 0;
    $errors = array();
    $logs = array();

    // endpoint único de partidas (/v4/matches) com filtro de data e ligas
    $leagues_string = implode( ',', $leagues_to_sync );
    $url = add_query_arg( array(
        'dateFrom'     => $data_sincronizacao,
        'dateTo'       => $data_sincronizacao,
        'competitions' => $leagues_string
    ), 'https://api.football-data.org/v4/matches' );

    $response = wp_remote_get( $url, array(
        'timeout' => 30,
        'headers' => array(
            'X-Auth-Token' => $api_key
        )
    ));

    if ( is_wp_error( $response ) ) {
        return array( 'success' => false, 'message' => 'Erro na requisição: ' . $response->get_error_message() );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Tratar erros da API
    if ( isset( $data['errorCode'] ) || ( isset( $data['message'] ) && !isset( $data['matches'] ) ) ) {
        $api_err = isset( $data['message'] ) ? $data['message'] : 'Erro desconhecido da API.';
        return array( 'success' => false, 'message' => 'Erro retornado pela API: ' . $api_err );
    }

    if ( ! isset( $data['matches'] ) || ! is_array( $data['matches'] ) ) {
        return array( 'success' => false, 'message' => 'A resposta da API não contém nenhuma partida.' );
    }

    $matches = $data['matches'];

    foreach ( $matches as $item ) {
        $match_id   = $item['id'];
        $home_name  = ! empty( $item['homeTeam']['shortName'] ) ? $item['homeTeam']['shortName'] : $item['homeTeam']['name'];
        $away_name  = ! empty( $item['awayTeam']['shortName'] ) ? $item['awayTeam']['shortName'] : $item['awayTeam']['name'];
        $home_logo  = $item['homeTeam']['crest'];
        $away_logo  = $item['awayTeam']['crest'];
        
        $goals_home = isset( $item['score']['fullTime']['home'] ) ? $item['score']['fullTime']['home'] : '';
        $goals_away = isset( $item['score']['fullTime']['away'] ) ? $item['score']['fullTime']['away'] : '';
        
        $status_api = $item['status'];
        $venue      = isset( $item['venue'] ) ? $item['venue'] : '';
        $matchday   = isset( $item['matchday'] ) ? $item['matchday'] : '';
        $stage_api  = isset( $item['stage'] ) ? $item['stage'] : '';

        // Converter Horário UTC para America/Sao_Paulo
        $fixture_date_raw = $item['utcDate']; // Ex: 2026-05-21T18:00:00Z
        try {
            $utc_date = new DateTime( $fixture_date_raw, new DateTimeZone( 'UTC' ) );
            $utc_date->setTimezone( new DateTimeZone( 'America/Sao_Paulo' ) );
            $game_date = $utc_date->format( 'Y-m-d' );
            $game_time = $utc_date->format( 'H\hi' ); // Ex: 19h30
        } catch ( Exception $e ) {
            $game_date = $data_sincronizacao;
            $game_time = '00h00';
        }

        // Mapear Status do Jogo
        $status_jogo = 'Agendado';
        if ( in_array( $status_api, array( 'IN_PLAY', 'PAUSED' ) ) ) {
            $status_jogo = 'Ao Vivo';
        } elseif ( in_array( $status_api, array( 'FINISHED', 'POSTPONED', 'CANCELLED', 'SUSPENDED' ) ) ) {
            $status_jogo = 'Encerrado';
        }

        // Verificar se o post do jogo já existe pelo football_data_match_id
        $existing_posts = get_posts( array(
            'post_type'      => 'jogo',
            'meta_key'       => 'football_data_match_id',
            'meta_value'     => $match_id,
            'posts_per_page' => 1,
            'post_status'    => 'any'
        ));

        $post_title = "{$home_name} x {$away_name}";
        $is_new = true;

        if ( ! empty( $existing_posts ) ) {
            $post_id = $existing_posts[0]->ID;
            $is_new = false;
            
            wp_update_post( array(
                'ID'         => $post_id,
                'post_title' => $post_title
            ));
        } else {
            // Inserir novo jogo
            $post_id = wp_insert_post( array(
                'post_title'  => $post_title,
                'post_status' => 'publish',
                'post_type'   => 'jogo'
            ));
        }

        if ( ! is_wp_error( $post_id ) ) {
            // Atualizar Metadados
            update_post_meta( $post_id, 'football_data_match_id', $match_id );
            update_post_meta( $post_id, 'time_casa', $home_name );
            update_post_meta( $post_id, 'time_fora', $away_name );
            update_post_meta( $post_id, 'escudo_casa', $home_logo );
            update_post_meta( $post_id, 'escudo_fora', $away_logo );
            update_post_meta( $post_id, 'placar_casa', $goals_home );
            update_post_meta( $post_id, 'placar_fora', $goals_away );
            update_post_meta( $post_id, 'status_jogo', $status_jogo );
            update_post_meta( $post_id, 'data_jogo', $game_date );
            update_post_meta( $post_id, 'horario', $game_time );
            update_post_meta( $post_id, 'estadio', $venue );
            
            // Mapear rodada ou fase do campeonato
            $rodada_exibida = '';
            if ( $stage_api === 'REGULAR_SEASON' && ! empty( $matchday ) ) {
                $rodada_exibida = "{$matchday}ª Rodada";
            } else {
                // Traduzir fases eliminatórias/copa
                $fases = array(
                    'GROUP_STAGE'     => 'Fase de Grupos',
                    'ROUND_OF_16'     => 'Oitavas de Final',
                    'QUARTER_FINALS'  => 'Quartas de Final',
                    'SEMI_FINALS'     => 'Semifinal',
                    'FINAL'           => 'Final'
                );
                $rodada_exibida = isset( $fases[$stage_api] ) ? $fases[$stage_api] : $stage_api;
            }
            update_post_meta( $post_id, 'rodada', $rodada_exibida );

            // Associar à taxonomia de Campeonatos
            $comp_code = $item['competition']['code'];
            $league_name = isset( FOOTBALL_DATA_DEFAULT_LEAGUES[$comp_code] ) ? FOOTBALL_DATA_DEFAULT_LEAGUES[$comp_code] : $item['competition']['name'];
            
            $term = term_exists( $league_name, 'campeonato' );
            if ( ! $term ) {
                $term = wp_insert_term( $league_name, 'campeonato' );
            }
            
            $term_id = 0;
            if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
                $term_id = intval( $term['term_id'] );
            } elseif ( isset( $term->term_id ) ) {
                $term_id = intval( $term->term_id );
            }

            if ( $term_id > 0 ) {
                wp_set_object_terms( $post_id, $term_id, 'campeonato' );
            }

            if ( $is_new ) {
                $imported_count++;
                $logs[] = "➕ Importado: <strong>{$post_title}</strong> às {$game_time} ({$league_name})";
            } else {
                $updated_count++;
                $logs[] = "🔄 Atualizado: <strong>{$post_title}</strong> (Placar: {$goals_home}x{$goals_away} | Status: {$status_jogo})";
            }
        }
    }

    return array(
        'success'  => true,
        'imported' => $imported_count,
        'updated'  => $updated_count,
        'errors'   => $errors,
        'logs'     => $logs
    );
}

/**
 * Função otimizada para atualizar placares em lote
 * Simplesmente reutiliza a sincronização para a data de hoje, atualizando os posts existentes
 */
function api_football_atualizar_placares_ativos() {
    // Para a API football-data.org, como a requisição nos dá todos os jogos em 1 chamada só por dia,
    // a atualização de placares dos jogos ativos é idêntica a sincronizar os jogos de hoje.
    $res = api_football_sincronizar_jogos();
    if ( $res['success'] ) {
        return array(
            'success' => true,
            'updated' => $res['updated'],
            'logs'    => $res['logs']
        );
    }
    return $res;
}

/**
 * Renderizar a Página de Administração do Importador no WordPress
 */
function api_football_renderizar_pagina() {
    // Processar Formulário de Ações se enviado
    $sync_result = null;
    $action_type = '';

    if ( isset( $_POST['api_football_salvar_key'] ) ) {
        check_admin_referer( 'api_football_salvar_options' );
        update_option( 'api_football_key', sanitize_text_field( $_POST['api_football_key'] ) );
        
        $selected_leagues = isset( $_POST['api_football_leagues'] ) ? array_map( 'sanitize_text_field', $_POST['api_football_leagues'] ) : array();
        update_option( 'api_football_leagues', $selected_leagues );
        
        echo '<div class="notice notice-success is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
    }

    if ( isset( $_POST['api_football_sync_hoje'] ) ) {
        check_admin_referer( 'api_football_action_sync' );
        $action_type = 'sincronizar';
        $sync_result = api_football_sincronizar_jogos();
    }

    if ( isset( $_POST['api_football_sync_amanha'] ) ) {
        check_admin_referer( 'api_football_action_sync' );
        $action_type = 'sincronizar';
        
        // Pega data local de amanhã
        $timezone = new DateTimeZone( 'America/Sao_Paulo' );
        $amanha = new DateTime( 'tomorrow', $timezone );
        $sync_result = api_football_sincronizar_jogos( $amanha->format( 'Y-m-d' ) );
    }

    if ( isset( $_POST['api_football_update_placares'] ) ) {
        check_admin_referer( 'api_football_action_sync' );
        $action_type = 'placares';
        $sync_result = api_football_atualizar_placares_ativos();
    }

    // Obter valores atuais
    $api_key = get_option( 'api_football_key' );
    $configured_leagues = get_option( 'api_football_leagues', array_keys( FOOTBALL_DATA_DEFAULT_LEAGUES ) );
    ?>
    <div class="wrap" style="max-width: 900px; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">
        <h1 style="color: #e67e22; font-weight: 700; margin-bottom: 20px; display: flex; items-center: center; gap: 8px;">
            <span class="dashicons dashicons-update-alt" style="font-size: 32px; width: 32px; height: 32px; color: #e67e22;"></span> 
            Sincronizador Football-Data (Temporada 2026 Grátis)
        </h1>

        <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
            
            <!-- CARD 1: Configurações de API -->
            <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; color: #1e293b;">⚙️ Configurações da API</h2>
                <form method="post" action="">
                    <?php wp_nonce_field( 'api_football_salvar_options' ); ?>
                    
                    <div style="margin-bottom: 20px;">
                        <label for="api_football_key" style="display: block; font-weight: bold; margin-bottom: 8px; color: #475569;">Seu API Token (Football-Data.org):</label>
                        <input type="password" id="api_football_key" name="api_football_key" value="<?php echo esc_attr( $api_key ); ?>" style="width: 100%; max-width: 400px; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;" placeholder="Insira seu Token da API">
                        <p class="description" style="margin-top: 5px;">Disponível após o registro gratuito em <a href="https://www.football-data.org/client/register" target="_blank">football-data.org/client/register</a>.</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #475569;">Campeonatos para Importar (Plano Grátis):</label>
                        <div style="display: flex; flex-direction: column; gap: 8px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <?php foreach ( FOOTBALL_DATA_DEFAULT_LEAGUES as $code => $name ) : ?>
                                <label style="font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="api_football_leagues[]" value="<?php echo $code; ?>" <?php checked( in_array( $code, $configured_leagues ) ); ?>>
                                    <?php echo esc_html( $name ); ?> <span style="font-size: 11px; color: #64748b; font-weight: 450;">(Código: <?php echo $code; ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <input type="submit" name="api_football_salvar_key" class="button button-primary" style="background: #e67e22; border-color: #d35400; font-weight: bold; text-shadow: none;" value="Salvar Configurações">
                </form>
            </div>

            <!-- CARD 2: Ações Rápidas de Sincronização -->
            <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; color: #1e293b;">⚡ Painel de Ações Rápidas</h2>
                
                <?php if ( empty( $api_key ) ) : ?>
                    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                        <span style="color: #991b1b; font-weight: 600;">Atenção:</span> Configure seu Token da API acima antes de tentar realizar a sincronização.
                    </div>
                <?php endif; ?>

                <form method="post" action="" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php wp_nonce_field( 'api_football_action_sync' ); ?>
                    
                    <input type="submit" name="api_football_sync_hoje" class="button button-large" style="background: #10b981; border-color: #059669; color: #fff; font-weight: bold; text-shadow: none;" value="Sincronizar Jogos de Hoje" <?php disabled( empty( $api_key ) ); ?>>
                    
                    <input type="submit" name="api_football_sync_amanha" class="button button-large" style="background: #3b82f6; border-color: #2563eb; color: #fff; font-weight: bold; text-shadow: none;" value="Sincronizar Jogos de Amanhã" <?php disabled( empty( $api_key ) ); ?>>
                    
                    <input type="submit" name="api_football_update_placares" class="button button-large" style="background: #8b5cf6; border-color: #7c3aed; color: #fff; font-weight: bold; text-shadow: none;" value="Atualizar Placares de Hoje" <?php disabled( empty( $api_key ) ); ?>>
                </form>

                <div style="margin-top: 15px; font-size: 12px; color: #64748b; background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                    💡 <strong>Consumo de Limite:</strong> A API Gratuita permite até <strong>10 chamadas por minuto</strong>. Cada sincronização ou atualização de placares realizada aqui consome <strong>apenas 1 chamada no total</strong> para trazer todas as ligas selecionadas juntas!
                </div>
            </div>

            <!-- CARD 3: Resultados da Última Execução -->
            <?php if ( $sync_result ) : ?>
                <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 style="margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; color: #1e293b;">📊 Relatório da Execução</h2>
                    
                    <?php if ( ! $sync_result['success'] ) : ?>
                        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px; border-radius: 4px; color: #991b1b; font-weight: 500;">
                            ❌ Falha: <?php echo esc_html( $sync_result['message'] ); ?>
                        </div>
                    <?php else : ?>
                        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <?php if ( $action_type === 'sincronizar' ) : ?>
                                <span style="background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 13px;">
                                    Criados: <?php echo intval( $sync_result['imported'] ); ?>
                                </span>
                                <span style="background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 13px;">
                                    Atualizados/Sincronizados: <?php echo intval( $sync_result['updated'] ); ?>
                                </span>
                            <?php else : ?>
                                <span style="background: #ede9fe; color: #5b21b6; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 13px;">
                                    Placares Atualizados: <?php echo intval( $sync_result['updated'] ); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! empty( $sync_result['errors'] ) ) : ?>
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px; color: #92400e; font-weight: 500; margin-bottom: 15px;">
                                ⚠️ <strong>Avisos/Erros:</strong>
                                <ul style="margin: 5px 0 0 15px; padding: 0;">
                                    <?php foreach ( $sync_result['errors'] as $err ) : ?>
                                        <li><?php echo esc_html( $err ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div style="max-height: 250px; overflow-y: auto; background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; line-height: 1.6;">
                            <span style="color: #94a3b8;">[<?php echo date( 'H:i:s' ); ?>] Iniciando logs do processo...</span><br>
                            <?php if ( empty( $sync_result['logs'] ) ) : ?>
                                <span style="color: #e2e8f0;">Nenhum jogo novo ou atualização de placar processada para esta data.</span>
                            <?php else : ?>
                                <?php foreach ( $sync_result['logs'] as $log ) : ?>
                                    <span>[<?php echo date( 'H:i:s' ); ?>] <?php echo $log; ?></span><br>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <span style="color: #94a3b8;">[<?php echo date( 'H:i:s' ); ?>] Processo concluído com sucesso.</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

<?php
/**
 * Template Name: Brasileirão Hub
 */
get_header();

// Lógica de limpeza de cache de transients (Force Update) para administradores
if ( current_user_can( 'manage_options' ) && isset( $_GET['clear_brasileirao_cache'] ) ) {
    delete_transient( 'api_football_bsa_standings' );
    delete_transient( 'api_football_bsa_scorers' );
    delete_transient( 'api_football_bsa_matches' );
    wp_redirect( remove_query_arg( 'clear_brasileirao_cache' ) );
    exit;
}

// Obter dados da API (utiliza cache interno de 12 horas via Transients)
$standings_data = api_football_obter_classificacao_brasileirao();
$scorers_data = api_football_obter_artilharia_brasileirao();
$matches_data = api_football_obter_jogos_brasileirao();

$is_error = is_wp_error( $standings_data ) || is_wp_error( $scorers_data ) || is_wp_error( $matches_data );
$error_message = '';

if ( $is_error ) {
    if ( is_wp_error( $standings_data ) ) {
        $error_message = $standings_data->get_error_message();
    } elseif ( is_wp_error( $scorers_data ) ) {
        $error_message = $scorers_data->get_error_message();
    } else {
        $error_message = $matches_data->get_error_message();
    }
}

// Obter tabela de classificação
$tabela_rows = array();
if ( ! $is_error && is_array( $standings_data ) ) {
    foreach ( $standings_data as $st ) {
        if ( isset( $st['type'] ) && $st['type'] === 'TOTAL' ) {
            $tabela_rows = $st['table'];
            break;
        }
    }
    if ( empty( $tabela_rows ) && isset( $standings_data[0]['table'] ) ) {
        $tabela_rows = $standings_data[0]['table'];
    }
}

// Obter artilharia
$artilheiros = array();
if ( ! $is_error && is_array( $scorers_data ) ) {
    $artilheiros = $scorers_data;
}

// Obter jogos e agrupar por rodada
$matches_list = array();
$current_matchday = 1;
if ( ! $is_error && is_array( $matches_data ) ) {
    $matches_list = $matches_data['matches'];
    $current_matchday = intval( $matches_data['currentMatchday'] );
}

$rodadas_matches = array();
if ( is_array( $matches_list ) ) {
    foreach ( $matches_list as $match ) {
        $r = intval( $match['matchday'] );
        if ( ! isset( $rodadas_matches[$r] ) ) {
            $rodadas_matches[$r] = array();
        }
        $rodadas_matches[$r][] = $match;
    }
}
ksort( $rodadas_matches );

// Mapear jogos do WordPress (CPT jogo) com a API para exibir canais cadastrados localmente e links internos
$local_games_mapped = array();
if ( ! $is_error ) {
    $local_games = get_posts( array(
        'post_type' => 'jogo',
        'posts_per_page' => -1,
    ));
    foreach ( $local_games as $lg ) {
        $match_api_id = get_post_meta( $lg->ID, 'football_data_match_id', true );
        $time_casa = mb_strtolower( trim( get_post_meta( $lg->ID, 'time_casa', true ) ) );
        $time_fora = mb_strtolower( trim( get_post_meta( $lg->ID, 'time_fora', true ) ) );
        $data_jogo = get_post_meta( $lg->ID, 'data_jogo', true ); // Y-m-d
        
        $channels = wp_get_post_terms( $lg->ID, 'canal', array( 'fields' => 'names' ) );
        $channels_str = ! empty( $channels ) ? implode( ', ', $channels ) : '';

        $game_data = array(
            'link' => get_permalink( $lg->ID ),
            'channels' => $channels_str
        );

        // 1. Mapear por ID da API (se existir)
        if ( $match_api_id ) {
            $local_games_mapped['id_' . $match_api_id] = $game_data;
        }

        // 2. Mapear por combinação de Times + Data
        if ( $time_casa && $time_fora && $data_jogo ) {
            $slug_date_key = sanitize_title( $time_casa ) . '_' . sanitize_title( $time_fora ) . '_' . $data_jogo;
            $local_games_mapped['slug_date_' . $slug_date_key] = $game_data;
        }

        // 3. Mapear por combinação de Times (sem data - fallback)
        if ( $time_casa && $time_fora ) {
            $slug_teams_key = sanitize_title( $time_casa ) . '_' . sanitize_title( $time_fora );
            if ( ! isset( $local_games_mapped['slug_teams_' . $slug_teams_key] ) ) {
                $local_games_mapped['slug_teams_' . $slug_teams_key] = $game_data;
            }
        }
    }
}

// Função auxiliar para tradução e classes de status dos jogos
function bsa_obter_status_formatado( $status ) {
    $label = 'Agendado';
    $class = 'bg-slate-800 text-slate-400';
    
    switch ( $status ) {
        case 'FINISHED':
            $label = 'Encerrado';
            $class = 'bg-slate-900 text-slate-500 border border-slate-800';
            break;
        case 'IN_PLAY':
            $label = 'Ao Vivo';
            $class = 'bg-red-600 text-white animate-pulse font-black';
            break;
        case 'PAUSED':
            $label = 'Intervalo';
            $class = 'bg-yellow-500 text-black font-black';
            break;
        case 'POSTPONED':
            $label = 'Adiado';
            $class = 'bg-slate-800 text-yellow-500';
            break;
        case 'CANCELLED':
            $label = 'Cancelado';
            $class = 'bg-slate-800 text-red-500';
            break;
    }
    
    return array( 'label' => $label, 'class' => $class );
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800;900&display=swap');
    
    .bsa-font { font-family: 'Outfit', sans-serif; }
    .glass-card {
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(39, 174, 96, 0.1);
    }
    .glass-card:hover {
        border-color: rgba(39, 174, 96, 0.3);
        box-shadow: 0 0 30px rgba(39, 174, 96, 0.08);
    }
    .text-gradient-green {
        background: linear-gradient(to right, #ffffff, #27ae60);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Cores das Zonas de Classificação */
    .border-libertadores { border-left: 4px solid #27ae60; }
    .border-prelibertadores { border-left: 4px solid #2ecc71; }
    .border-sulamericana { border-left: 4px solid #2980b9; }
    .border-rebaixamento { border-left: 4px solid #e74c3c; }

    /* Custom classes for tabs active state */
    .tab-btn.active {
        background-color: #E67E22;
        color: white;
        border-color: #E67E22;
        transform: translateY(-2px);
    }
</style>

<div class="bg-slate-950 text-white min-h-screen bsa-font">
    
    <!-- HERO SECTION -->
    <section class="relative py-16 overflow-hidden bg-gradient-to-b from-verde-menta/10 to-transparent">
        <div class="container mx-auto px-4 text-center relative z-10">
            <span class="inline-block bg-verde-menta/25 border border-verde-menta/45 text-verde-menta text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-4">
                🏆 Campeonato Brasileiro 2026
            </span>
            <h1 class="text-4xl md:text-7xl font-black mb-6 tracking-tighter italic uppercase text-gradient-green">
                Tabela e Jogos do Brasileirão
            </h1>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto font-medium">
                Acompanhe a classificação completa, os artilheiros da temporada e saiba onde assistir cada jogo ao vivo.
            </p>
        </div>
        
        <!-- ELEMENTO DECORATIVO -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 flex gap-4 opacity-5 pointer-events-none">
            <span class="text-[120px] font-black select-none uppercase italic text-white">BSA</span>
        </div>
    </section>

    <main class="container mx-auto px-4 pb-24">
        
        <!-- BOTÃO DE ATUALIZAÇÃO PARA ADMINISTRADOR -->
        <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <div class="flex justify-end mb-6">
                <a href="<?php echo esc_url( add_query_arg( 'clear_brasileirao_cache', '1' ) ); ?>" class="bg-slate-900 border border-slate-800 hover:bg-laranja hover:border-laranja text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg">
                    <i class="fas fa-sync-alt"></i> Limpar Cache e Atualizar Dados (API)
                </a>
            </div>
        <?php endif; ?>

        <!-- TRATAMENTO DE ERROS -->
        <?php if ( $is_error ) : ?>
            <section class="bg-red-950/20 border border-red-900/50 p-8 rounded-3xl text-center max-w-3xl mx-auto my-12">
                <div class="w-16 h-16 bg-red-650/10 border border-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Erro ao carregar dados do campeonato</h3>
                <p class="text-gray-400 text-sm mb-6">
                    A API de futebol está temporariamente indisponível ou atingiu o limite de taxa. Por favor, tente novamente em alguns instantes.
                </p>
                <?php if ( current_user_can( 'manage_options' ) && ! empty( $error_message ) ) : ?>
                    <div class="bg-black/40 text-red-400 font-mono text-[11px] p-4 rounded-xl text-left overflow-x-auto mb-4">
                        <strong>Log de erro do administrador:</strong> <?php echo esc_html( $error_message ); ?>
                    </div>
                <?php endif; ?>
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="inline-block bg-white text-black font-black uppercase text-xs px-6 py-3 rounded-full hover:bg-laranja hover:text-white transition">
                    Voltar para a Página Inicial
                </a>
            </section>
        <?php else : ?>

            <!-- NAVIGATION TABS -->
            <section class="flex justify-center mb-10">
                <div class="bg-slate-900/60 p-1.5 rounded-2xl flex gap-1.5 border border-slate-800/80">
                    <button data-tab="jogos" class="tab-btn active px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-sm"></i> Jogos e Rodadas
                    </button>
                    <button data-tab="classificacao" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-table text-sm"></i> Classificação
                    </button>
                    <button data-tab="artilharia" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-fire text-sm"></i> Artilharia
                    </button>
                </div>
            </section>

            <!-- TAB CONTENT: CLASSIFICACAO -->
            <section id="tab-classificacao" class="tab-content hidden transition-all duration-300">
                
                <!-- LEGENDA DAS ZONAS -->
                <div class="flex flex-wrap gap-4 mb-6 text-[10px] uppercase font-bold text-gray-400 bg-slate-900/25 p-4 rounded-2xl border border-slate-900 justify-center">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#27ae60] rounded-sm"></span>
                        Fase de Grupos Libertadores (G4)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#2ecc71] rounded-sm"></span>
                        Qualificação Libertadores (G6)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#2980b9] rounded-sm"></span>
                        Copa Sul-Americana (G12)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#e74c3c] rounded-sm"></span>
                        Rebaixamento (Z4)
                    </div>
                </div>

                <div class="glass-card rounded-3xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-gray-400 uppercase text-[10px] border-b border-slate-800/80 bg-slate-950/40">
                                    <th class="px-6 py-4 font-black text-white text-left tracking-wider">Posição</th>
                                    <th class="px-2 py-4 text-center w-12 font-bold">P</th>
                                    <th class="px-2 py-4 text-center w-12 font-bold">J</th>
                                    <th class="px-2 py-4 text-center w-12 font-bold">V</th>
                                    <th class="px-2 py-4 text-center w-12 font-bold">E</th>
                                    <th class="px-2 py-4 text-center w-12 font-bold">D</th>
                                    <th class="px-3 py-4 text-center w-14 font-bold">GP</th>
                                    <th class="px-3 py-4 text-center w-14 font-bold">GC</th>
                                    <th class="px-3 py-4 text-center w-14 font-bold">SG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ( ! empty( $tabela_rows ) ) :
                                    foreach ( $tabela_rows as $row ) :
                                        $pos = intval( $row['position'] );
                                        
                                        // Definir classe de borda com base na posição
                                        $zona_class = '';
                                        if ( $pos <= 4 ) {
                                            $zona_class = 'border-libertadores bg-emerald-500/5';
                                        } elseif ( $pos <= 6 ) {
                                            $zona_class = 'border-prelibertadores bg-teal-500/5';
                                        } elseif ( $pos >= 7 && $pos <= 12 ) {
                                            $zona_class = 'border-sulamericana bg-blue-500/5';
                                        } elseif ( $pos >= 17 ) {
                                            $zona_class = 'border-rebaixamento bg-red-500/5';
                                        }
                                ?>
                                        <tr class="border-b border-slate-900/60 hover:bg-slate-800/30 transition-colors <?php echo $zona_class; ?>">
                                            <td class="px-6 py-3.5 flex items-center gap-3.5">
                                                <span class="w-5 text-right font-black text-white text-sm <?php echo ( $pos <= 4 || $pos >= 17 ) ? 'text-opacity-100' : 'text-gray-400'; ?>">
                                                    <?php echo $pos; ?>
                                                </span>
                                                <img src="<?php echo esc_url( $row['team']['crest'] ); ?>" class="w-6 h-6 object-contain flex-shrink-0" alt="" loading="lazy">
                                                <span class="font-black text-white text-sm tracking-wide uppercase">
                                                    <?php echo esc_html( $row['team']['shortName'] ?: $row['team']['name'] ); ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3.5 text-center font-black text-white bg-laranja/10 rounded-md text-sm"><?php echo $row['points']; ?></td>
                                            <td class="px-2 py-3.5 text-center text-gray-300 font-bold"><?php echo $row['playedGames']; ?></td>
                                            <td class="px-2 py-3.5 text-center text-gray-300"><?php echo $row['won']; ?></td>
                                            <td class="px-2 py-3.5 text-center text-gray-400"><?php echo $row['draw']; ?></td>
                                            <td class="px-2 py-3.5 text-center text-gray-400"><?php echo $row['lost']; ?></td>
                                            <td class="px-3 py-3.5 text-center text-gray-400"><?php echo $row['goalsFor']; ?></td>
                                            <td class="px-3 py-3.5 text-center text-gray-400"><?php echo $row['goalsAgainst']; ?></td>
                                            <td class="px-3 py-3.5 text-center font-bold text-sm <?php echo ( $row['goalDifference'] > 0 ) ? 'text-emerald-500' : ( ( $row['goalDifference'] < 0 ) ? 'text-red-500' : 'text-gray-450' ); ?>">
                                                <?php echo ( $row['goalDifference'] > 0 ? '+' : '' ) . $row['goalDifference']; ?>
                                            </td>
                                        </tr>
                                <?php 
                                    endforeach; 
                                else :
                                ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-12 text-gray-500 italic">Nenhum dado de classificação disponível.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- TAB CONTENT: JOGOS -->
            <section id="tab-jogos" class="tab-content transition-all duration-300">
                
                <!-- SELECTOR DE RODADAS -->
                <div class="flex items-center justify-between bg-slate-900/60 border border-slate-800/80 p-3.5 rounded-2xl mb-8 max-w-lg mx-auto">
                    <button id="btn-rodada-anterior" class="px-4 py-2 bg-slate-950/60 border border-slate-800 hover:bg-laranja hover:border-laranja text-white rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center gap-1.5">
                        <i class="fas fa-chevron-left text-[10px]"></i> Ant.
                    </button>
                    
                    <div class="text-center">
                        <select id="select-rodada" class="bg-slate-950 border border-slate-800 text-white font-black text-sm py-2 px-6 rounded-xl focus:outline-none focus:border-laranja cursor-pointer">
                            <?php for ($i = 1; $i <= 38; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected($i, $current_matchday); ?>><?php echo $i; ?>ª Rodada</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <button id="btn-rodada-proxima" class="px-4 py-2 bg-slate-950/60 border border-slate-800 hover:bg-laranja hover:border-laranja text-white rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center gap-1.5">
                        Próx. <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                </div>

                <!-- LISTA DE JOGOS DAS RODADAS -->
                <div id="rodadas-list-container">
                    <?php 
                    for ($r = 1; $r <= 38; $r++) :
                        $rodada_games = isset( $rodadas_matches[$r] ) ? $rodadas_matches[$r] : array();
                        $hidden_class = ( $r === $current_matchday ) ? '' : 'hidden';
                    ?>
                        <div id="rodada-panel-<?php echo $r; ?>" class="rodada-panel <?php echo $hidden_class; ?> grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <?php 
                            if ( ! empty( $rodada_games ) ) :
                                foreach ( $rodada_games as $match ) :
                                    $match_id = $match['id'];
                                    
                                    // Tratar status do jogo
                                    $status_info = bsa_obter_status_formatado( $match['status'] );
                                    
                                    // Tratar hora e data local brasileiras
                                    $game_date = 'Data Indefinida';
                                    $game_time = '00:00';
                                    $game_date_ymd = '';
                                    try {
                                        $utc_date = new DateTime( $match['utcDate'], new DateTimeZone( 'UTC' ) );
                                        $utc_date->setTimezone( new DateTimeZone( 'America/Sao_Paulo' ) );
                                        $game_date = $utc_date->format( 'd/m/Y' );
                                        $game_time = $utc_date->format( 'H:i' );
                                        $game_date_ymd = $utc_date->format( 'Y-m-d' );
                                    } catch ( Exception $e ) {
                                        // fallback
                                    }
                                    
                                    // Verificar se o jogo existe localmente no CPT jogo (por ID, Slug de Data ou Fallback de Confronto)
                                    $home_short = mb_strtolower( trim( $match['homeTeam']['shortName'] ?: $match['homeTeam']['name'] ) );
                                    $away_short = mb_strtolower( trim( $match['awayTeam']['shortName'] ?: $match['awayTeam']['name'] ) );
                                    
                                    $slug_date_key = sanitize_title( $home_short ) . '_' . sanitize_title( $away_short ) . '_' . $game_date_ymd;
                                    $slug_teams_key = sanitize_title( $home_short ) . '_' . sanitize_title( $away_short );
                                    
                                    $is_local = false;
                                    $local_info = null;
                                    
                                    if ( isset( $local_games_mapped['id_' . $match_id] ) ) {
                                        $is_local = true;
                                        $local_info = $local_games_mapped['id_' . $match_id];
                                    } elseif ( ! empty( $game_date_ymd ) && isset( $local_games_mapped['slug_date_' . $slug_date_key] ) ) {
                                        $is_local = true;
                                        $local_info = $local_games_mapped['slug_date_' . $slug_date_key];
                                    } elseif ( isset( $local_games_mapped['slug_teams_' . $slug_teams_key] ) ) {
                                        $is_local = true;
                                        $local_info = $local_games_mapped['slug_teams_' . $slug_teams_key];
                                    }
                                    
                                    $local_link = $is_local ? $local_info['link'] : '#';
                                    $local_channels = $is_local ? $local_info['channels'] : '';
                                    $display_channels = ! empty( $local_channels ) ? $local_channels : 'Globo, SporTV, Premiere';
                            ?>
                                    <div class="glass-card rounded-3xl p-5 border border-slate-900/60 transition-all duration-300 relative flex flex-col justify-between overflow-hidden">
                                        
                                        <!-- Header do Jogo (Data, Hora e Status) -->
                                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-900/40">
                                            <span class="text-[10px] text-gray-500 font-bold tracking-wider uppercase flex items-center gap-1.5">
                                                <i class="far fa-calendar-alt text-laranja"></i> <?php echo esc_html( $game_date ); ?> às <?php echo esc_html( $game_time ); ?>
                                            </span>
                                            <span class="text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-wider <?php echo esc_attr( $status_info['class'] ); ?>">
                                                <?php echo esc_html( $status_info['label'] ); ?>
                                            </span>
                                        </div>

                                        <!-- Confronto: Times e Placar -->
                                        <div class="flex items-center justify-between gap-4 py-3">
                                            <!-- Time Casa -->
                                            <div class="flex-1 flex items-center gap-3">
                                                <img src="<?php echo esc_url( $match['homeTeam']['crest'] ); ?>" class="w-8 h-8 object-contain" alt="" loading="lazy">
                                                <span class="font-black text-white text-sm uppercase tracking-wide truncate max-w-[130px] md:max-w-none">
                                                    <?php echo esc_html( $match['homeTeam']['shortName'] ?: $match['homeTeam']['name'] ); ?>
                                                </span>
                                            </div>

                                            <!-- Placar -->
                                            <div class="flex items-center gap-2 bg-slate-950/70 py-1.5 px-3.5 rounded-2xl border border-slate-900">
                                                <span class="text-lg font-black text-white w-5 text-center">
                                                    <?php echo isset( $match['score']['fullTime']['home'] ) ? $match['score']['fullTime']['home'] : '-'; ?>
                                                </span>
                                                <span class="text-[9px] font-black text-gray-500 italic">X</span>
                                                <span class="text-lg font-black text-white w-5 text-center">
                                                    <?php echo isset( $match['score']['fullTime']['away'] ) ? $match['score']['fullTime']['away'] : '-'; ?>
                                                </span>
                                            </div>

                                            <!-- Time Fora -->
                                            <div class="flex-1 flex items-center justify-end gap-3 text-right">
                                                <span class="font-black text-white text-sm uppercase tracking-wide truncate max-w-[130px] md:max-w-none">
                                                    <?php echo esc_html( $match['awayTeam']['shortName'] ?: $match['awayTeam']['name'] ); ?>
                                                </span>
                                                <img src="<?php echo esc_url( $match['awayTeam']['crest'] ); ?>" class="w-8 h-8 object-contain" alt="" loading="lazy">
                                            </div>
                                        </div>

                                        <!-- ODDS DO JOGO -->
                                        <?php 
                                        $odd_casa = isset( $match['odds']['homeWin'] ) ? $match['odds']['homeWin'] : '';
                                        $odd_empate = isset( $match['odds']['draw'] ) ? $match['odds']['draw'] : '';
                                        $odd_fora = isset( $match['odds']['awayWin'] ) ? $match['odds']['awayWin'] : '';
                                        if ( ! empty( $odd_casa ) || ! empty( $odd_empate ) || ! empty( $odd_fora ) ) :
                                        ?>
                                            <div class="mt-3 bg-slate-950/40 p-2.5 rounded-2xl border border-slate-900/60 flex items-center justify-between gap-2">
                                                <div class="text-[8px] font-black uppercase text-gray-500 tracking-wider">Odds Médias</div>
                                                <div class="flex gap-1.5 text-[10px]">
                                                    <span class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-800 text-gray-400 font-bold">
                                                        1: <strong class="text-verde-menta font-black"><?php echo number_format($odd_casa, 2); ?></strong>
                                                    </span>
                                                    <span class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-800 text-gray-400 font-bold">
                                                        X: <strong class="text-laranja font-black"><?php echo number_format($odd_empate, 2); ?></strong>
                                                    </span>
                                                    <span class="bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-800 text-gray-400 font-bold">
                                                        2: <strong class="text-red-400 font-black"><?php echo number_format($odd_fora, 2); ?></strong>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Transmissão e Link -->
                                        <div class="mt-4 pt-3 border-t border-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <div class="text-[10px] text-gray-400 font-medium">
                                                <span class="text-laranja font-black uppercase text-[8px] block tracking-widest mb-0.5">Onde assistir</span>
                                                <span class="font-bold text-white"><?php echo esc_html( $display_channels ); ?></span>
                                            </div>
                                            
                                            <?php if ( $is_local ) : ?>
                                                <a href="<?php echo esc_url( $local_link ); ?>" class="w-full sm:w-auto bg-laranja hover:bg-orange-600 text-white font-black uppercase text-[10px] px-5 py-2.5 rounded-xl transition flex items-center justify-center gap-1.5 tracking-wider shadow-md">
                                                    Assistir Ao Vivo <i class="fas fa-play text-[8px]"></i>
                                                </a>
                                            <?php else : ?>
                                                <span class="w-full sm:w-auto bg-slate-900/80 border border-slate-800 text-gray-500 font-bold uppercase text-[9px] px-4 py-2.5 rounded-xl flex items-center justify-center gap-1.5 cursor-not-allowed">
                                                    Página a Criar <i class="fas fa-clock"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                            <?php 
                                endforeach;
                            else :
                            ?>
                                <div class="col-span-2 text-center py-12 text-gray-500 italic">Nenhum jogo cadastrado para esta rodada.</div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </section>

            <!-- TAB CONTENT: ARTILHARIA -->
            <section id="tab-artilharia" class="tab-content hidden transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if ( ! empty( $artilheiros ) ) :
                        $pos = 1;
                        foreach ( $artilheiros as $art ) :
                            $player_name = $art['player']['name'];
                            $team_name = $art['team']['shortName'] ?: $art['team']['name'];
                            $crest = $art['team']['crest'];
                            $goals = $art['goals'];
                            $assists = isset( $art['assists'] ) && $art['assists'] !== null ? $art['assists'] : 0;
                            $matches_played = isset( $art['playedMatches'] ) ? $art['playedMatches'] : '-';
                    ?>
                            <div class="glass-card rounded-3xl p-5 border border-slate-900/60 transition-all duration-300 flex items-center gap-4 group">
                                <div class="w-12 h-12 bg-laranja/10 border border-laranja/25 text-laranja font-black text-xl rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-105 transition-transform duration-300">
                                    <?php echo $pos; ?>º
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-black text-white text-base group-hover:text-laranja transition-colors uppercase tracking-tight truncate">
                                        <?php echo esc_html( $player_name ); ?>
                                    </h4>
                                    
                                    <div class="flex items-center gap-2 mt-1">
                                        <img src="<?php echo esc_url( $crest ); ?>" class="w-4 h-4 object-contain" alt="" loading="lazy">
                                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider truncate"><?php echo esc_html( $team_name ); ?></span>
                                    </div>
                                </div>
                                
                                <div class="text-right flex-shrink-0 bg-slate-950/60 border border-slate-900 p-2.5 rounded-2xl flex flex-col justify-center min-w-[70px]">
                                    <span class="text-xl font-black text-white leading-none block"><?php echo $goals; ?></span>
                                    <span class="text-[8px] font-black text-laranja uppercase tracking-widest block mt-1">Gols</span>
                                </div>
                            </div>
                    <?php 
                            $pos++;
                        endforeach;
                    else :
                    ?>
                        <div class="col-span-3 text-center py-12 text-gray-500 italic">Nenhum dado de artilharia disponível no momento.</div>
                    <?php endif; ?>
                </div>
            </section>

        <?php endif; ?>

        <!-- SEÇÃO DE FAQ (Otimização SEO) -->
        <section class="mt-20 border-t border-slate-900/60 pt-16 max-w-4xl mx-auto">
            <h3 class="text-2xl font-black uppercase italic tracking-tight text-white mb-8 text-center flex items-center justify-center gap-3">
                <span class="w-1.5 h-6 bg-laranja rounded-full shadow-[0_0_15px_#E67E22]"></span>
                Perguntas Frequentes — Brasileirão 2026
            </h3>

            <div class="space-y-4">
                <div class="glass-card rounded-2xl p-6 border border-slate-900/60">
                    <h4 class="text-base font-black text-laranja uppercase tracking-tight mb-2">
                        Como assistir aos jogos do Brasileirão 2026 ao vivo?
                    </h4>
                    <p class="text-sm text-gray-350 leading-relaxed">
                        Você pode acompanhar as transmissões oficiais do Brasileirão 2026 na TV aberta pela Rede Globo, na TV por assinatura pelo SporTV e Premiere (Pay-per-view), além das plataformas de streaming oficiais que detêm os direitos dos clubes mandantes.
                    </p>
                </div>

                <div class="glass-card rounded-2xl p-6 border border-slate-900/60">
                    <h4 class="text-base font-black text-laranja uppercase tracking-tight mb-2">
                        Onde encontrar a classificação atualizada do Brasileirão 2026?
                    </h4>
                    <p class="text-sm text-gray-350 leading-relaxed">
                        A classificação completa e em tempo real do Campeonato Brasileiro Série A 2026 pode ser encontrada diretamente na nossa página do Brasileirão Hub, com dados oficiais de pontos, jogos, vitórias e saldo de gols.
                    </p>
                </div>

                <div class="glass-card rounded-2xl p-6 border border-slate-900/60">
                    <h4 class="text-base font-black text-laranja uppercase tracking-tight mb-2">
                        Quantos times se classificam para a Libertadores no Brasileirão?
                    </h4>
                    <p class="text-sm text-gray-350 leading-relaxed">
                        Geralmente, os 6 primeiros colocados (G6) se classificam para a Copa Libertadores da América, sendo que os 4 primeiros garantem vaga direta na fase de grupos e o 5º e 6º disputam a fase preliminar. Esse número pode aumentar dependendo dos campeões da Copa do Brasil, Libertadores e Sul-Americana.
                    </p>
                </div>
            </div>
        </section>

    </main>
</div>

<!-- JAVASCRIPT DE SELEÇÃO DE ABAS E NAVEGAÇÃO DE RODADAS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================
    // CONTROLE DE ABAS (TABS)
    // =====================================
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Desativar todos os botões e abas
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.add('hidden'));

            // Ativar a aba correspondente
            this.classList.add('active');
            const activeContent = document.getElementById('tab-' + targetTab);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
        });
    });

    // =====================================
    // CONTROLE DE RODADAS
    // =====================================
    const selectRodada = document.getElementById('select-rodada');
    const btnAnterior = document.getElementById('btn-rodada-anterior');
    const btnProxima = document.getElementById('btn-rodada-proxima');
    const panels = document.querySelectorAll('.rodada-panel');

    if (selectRodada) {
        let currentRodadaIndex = parseInt(selectRodada.value);

        function mostrarRodada(index) {
            if (index < 1 || index > 38) return;

            currentRodadaIndex = index;
            selectRodada.value = index;

            // Ocultar todos os painéis e mostrar apenas o selecionado
            panels.forEach(p => p.classList.add('hidden'));
            const activePanel = document.getElementById('rodada-panel-' + index);
            if (activePanel) {
                activePanel.classList.remove('hidden');
            }

            // Habilitar/Desabilitar botões nos extremos
            btnAnterior.disabled = (index === 1);
            btnProxima.disabled = (index === 38);

            // Estilizar botões desabilitados
            if (index === 1) {
                btnAnterior.classList.add('opacity-40', 'cursor-not-allowed');
            } else {
                btnAnterior.classList.remove('opacity-40', 'cursor-not-allowed');
            }

            if (index === 38) {
                btnProxima.classList.add('opacity-40', 'cursor-not-allowed');
            } else {
                btnProxima.classList.remove('opacity-40', 'cursor-not-allowed');
            }
        }

        // Event listener do dropdown
        selectRodada.addEventListener('change', function() {
            mostrarRodada(parseInt(this.value));
        });

        // Event listener do botão Anterior
        btnAnterior.addEventListener('click', function() {
            if (currentRodadaIndex > 1) {
                mostrarRodada(currentRodadaIndex - 1);
            }
        });

        // Event listener do botão Próxima
        btnProxima.addEventListener('click', function() {
            if (currentRodadaIndex < 38) {
                mostrarRodada(currentRodadaIndex + 1);
            }
        });

        // Inicializar os botões corretos
        mostrarRodada(currentRodadaIndex);
    }
});
</script>

<?php
get_footer();

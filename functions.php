<?php
/**
 * Funções e definições do tema Assistir Jogos
 */

if ( ! function_exists( 'assistir_jogos_setup' ) ) :
    function assistir_jogos_setup() {
        // Suporte a títulos dinâmicos
        add_theme_support( 'title-tag' );
        // Suporte a imagens destacadas
        add_theme_support( 'post-thumbnails' );
        // Suporte a Logo Customizada (Habilita a Identidade do Site no Personalizar)
        add_theme_support( 'custom-logo' );
        // Menus
        register_nav_menus( array(
            'primary' => __( 'Menu Principal', 'themeassistirjogos' ),
        ) );
    }
endif;
add_action( 'after_setup_theme', 'assistir_jogos_setup' );

// Registrar CPT Jogos
function registrar_cpt_jogos() {
    $labels = array(
        'name'               => 'Jogos',
        'singular_name'      => 'Jogo',
        'menu_name'          => 'Jogos',
        'add_new'            => 'Adicionar Novo',
        'add_new_item'       => 'Adicionar Novo Jogo',
        'edit_item'          => 'Editar Jogo',
        'all_items'          => 'Todos os Jogos',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'show_in_rest'       => true, // Importante para o JS consumir depois
    );
    register_post_type( 'jogo', $args );
}
add_action( 'init', 'registrar_cpt_jogos' );

// Registrar CPT Jogos da Copa (Separado para organização premium)
function registrar_cpt_copa() {
    $labels = array(
        'name'               => 'Copa do Mundo',
        'singular_name'      => 'Jogo da Copa do Mundo',
        'menu_name'          => 'Copa do Mundo',
        'add_new'            => 'Adicionar Jogo',
        'add_new_item'       => 'Adicionar Novo Jogo da Copa do Mundo',
        'edit_item'          => 'Editar Jogo da Copa do Mundo',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_icon'          => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZmlsbD0iI2ZmZDM0ZiIgZD0iTTE1IDRWM2MwLS41NS0uNDUtMS0xLTFINmMtLjU1IDAtMSAuNDUtMSAxdjFIMnY0YzAgMi4yMSAxLjc5IDQgNCA0aDEuMWMuNDIgMS4wNyAxLjQxIDEuODYgMi41OSAxLjk4VjE2SDd2Mmg2di0yaC0yLjY5di0xLjAyYzEuMTgtLjEyIDIuMTctLjkxIDIuNTktMS45OEgxNGMyLjIxIDAgNC0xLjc5IDQtNFY0aC0zeiI+PC9wYXRoPjwvc3ZnPg==',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'show_in_rest'       => true,
        'rewrite'            => array('slug' => 'copa'),
    );
    register_post_type( 'jogo_copa', $args );
}
add_action( 'init', 'registrar_cpt_copa' );

// Registrar CPT Seleções
function registrar_cpt_selecao() {
    $labels = array(
        'name' => 'Seleções',
        'singular_name' => 'Seleção',
        'menu_name' => 'Seleções',
        'add_new' => 'Adicionar Seleção',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-flag',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'selecao'),
    );
    register_post_type( 'selecao', $args );
}
add_action( 'init', 'registrar_cpt_selecao' );

// Registrar CPT Jogadores
function registrar_cpt_jogador() {
    $labels = array(
        'name' => 'Jogadores',
        'singular_name' => 'Jogador',
        'menu_name' => 'Jogadores',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-admin-users',
        'supports' => array( 'title', 'thumbnail' ),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'jogador'),
    );
    register_post_type( 'jogador', $args );
}
add_action( 'init', 'registrar_cpt_jogador' );

// Registrar Taxonomia Esportes
function registrar_taxonomia_esportes() {
    register_taxonomy('esporte', 'jogo', array(
        'labels' => array('name' => 'Esportes', 'singular_name' => 'Esporte'),
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'registrar_taxonomia_esportes');


// Registrar Taxonomia Campeonatos
function registrar_taxonomia_campeonatos() {
    register_taxonomy('campeonato', 'jogo', array(
        'labels' => array('name' => 'Campeonatos', 'singular_name' => 'Campeonato'),
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'registrar_taxonomia_campeonatos');

// Registrar Taxonomia Canais
function registrar_taxonomia_canais() {
    register_taxonomy('canal', 'jogo', array(
        'labels' => array('name' => 'Canais', 'singular_name' => 'Canal'),
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'registrar_taxonomia_canais');

// Registrar Taxonomia Eventos (Copa, Olimpíadas, etc)
function registrar_taxonomia_eventos() {
    register_taxonomy('evento', 'jogo', array(
        'labels' => array('name' => 'Eventos Especiais', 'singular_name' => 'Evento'),
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'registrar_taxonomia_eventos');

/**
 * CAMPOS EXTRAS PARA CANAIS (Logo e Link Afiliado)
 */
function adicionar_campos_taxonomia_canal($term) {
    $logo = (is_object($term)) ? get_term_meta($term->term_id, 'logo_url', true) : '';
    $link = (is_object($term)) ? get_term_meta($term->term_id, 'afiliado_url', true) : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label>URL do Logo:</label></th>
        <td>
            <input type="text" name="logo_url" id="logo_url" value="<?php echo esc_attr($logo); ?>">
            <button type="button" class="button select-img" data-target="#logo_url">Selecionar</button>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label>Link de Afiliado:</label></th>
        <td><input type="text" name="afiliado_url" value="<?php echo esc_attr($link); ?>"></td>
    </tr>
    <?php
}
add_action('canal_edit_form_fields', 'adicionar_campos_taxonomia_canal');
add_action('canal_add_form_fields', 'adicionar_campos_taxonomia_canal');

function salvar_campos_taxonomia_canal($term_id) {
    if (isset($_POST['logo_url'])) update_term_meta($term_id, 'logo_url', esc_url_raw($_POST['logo_url']));
    if (isset($_POST['afiliado_url'])) update_term_meta($term_id, 'afiliado_url', esc_url_raw($_POST['afiliado_url']));
}
add_action('edited_canal', 'salvar_campos_taxonomia_canal');
add_action('create_canal', 'salvar_campos_taxonomia_canal');

/**
 * META BOXES PARA JOGOS
 */
function adicionar_meta_boxes_jogos() {
    // Meta box comum para ambos
    add_meta_box('dados_jogo', 'Informações do Jogo', 'renderizar_meta_box_jogo', array('jogo', 'jogo_copa'), 'normal', 'high');
}
add_action('add_meta_boxes', 'adicionar_meta_boxes_jogos');

function renderizar_meta_box_jogo($post) {
    // Adicionar Nonce para segurança
    wp_nonce_field('salvar_dados_jogo', 'jogo_nonce');

    $time_casa = get_post_meta($post->ID, 'time_casa', true);
    $time_fora = get_post_meta($post->ID, 'time_fora', true);
    $horario = get_post_meta($post->ID, 'horario', true);
    $data_jogo = get_post_meta($post->ID, 'data_jogo', true) ?: date('Y-m-d');
    $time_casa_id = get_post_meta($post->ID, 'time_casa_id', true);
    $time_fora_id = get_post_meta($post->ID, 'time_fora_id', true);
    $canais_selecionados = wp_get_post_terms($post->ID, 'canal', array('fields' => 'ids'));
    $link_transmissao = get_post_meta($post->ID, 'link_transmissao', true);
    $escudo_casa = get_post_meta($post->ID, 'escudo_casa', true);
    $escudo_fora = get_post_meta($post->ID, 'escudo_fora', true);
    $oddCasa = get_post_meta($post->ID, 'oddCasa', true);
    $oddEmpate = get_post_meta($post->ID, 'oddEmpate', true);
    $oddFora = get_post_meta($post->ID, 'oddFora', true);
    $placar_casa = get_post_meta($post->ID, 'placar_casa', true);
    $placar_fora = get_post_meta($post->ID, 'placar_fora', true);
    $status_jogo = get_post_meta($post->ID, 'status_jogo', true) ?: 'Agendado';
    $analise = get_post_meta($post->ID, 'analise_jogo', true);
    $is_copa = ($post->post_type === 'jogo_copa');
    ?>
    <style>
        .wp-admin-field { margin-bottom: 15px; } 
        .wp-admin-field label { display: block; font-weight: bold; margin-bottom: 5px; } 
        .wp-admin-field input, .wp-admin-field select { width: 100%; padding: 8px; }
        .copa-only { background: #fff9e6; padding: 15px; border: 1px solid #ffeeba; border-radius: 8px; margin-bottom: 20px; }
    </style>

    <?php if ($is_copa) : ?>
        <div class="copa-only">
            <h3 style="margin-top:0;">🏆 Dados Exclusivos da Copa</h3>
            <div class="wp-admin-field">
                <label>Time Casa (Selecione a Seleção):</label>
                <select name="time_casa_id" style="width:100%;">
                    <option value="">-- Selecione uma Seleção Cadastrada --</option>
                    <?php
                    $selecoes = get_posts(array('post_type' => 'selecao', 'posts_per_page' => -1));
                    foreach ($selecoes as $sel) {
                        echo '<option value="'.$sel->ID.'" '.selected($time_casa_id, $sel->ID, false).'>'.$sel->post_title.'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="wp-admin-field">
                <label>Time Fora (Selecione a Seleção):</label>
                <select name="time_fora_id" style="width:100%;">
                    <option value="">-- Selecione uma Seleção Cadastrada --</option>
                    <?php
                    foreach ($selecoes as $sel) {
                        echo '<option value="'.$sel->ID.'" '.selected($time_fora_id, $sel->ID, false).'>'.$sel->post_title.'</option>';
                    }
                    ?>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <div class="wp-admin-field" style="flex:1;">
                    <label>Cidade Sede:</label>
                    <input type="text" name="cidade_sede" value="<?php echo esc_attr(get_post_meta($post->ID, 'cidade_sede', true)); ?>" placeholder="Ex: New York">
                </div>
                <div class="wp-admin-field" style="flex:1;">
                    <label>País Sede:</label>
                    <select name="pais_sede">
                        <option value="">Nenhum / Outros</option>
                        <option value="EUA" <?php selected(get_post_meta($post->ID, 'pais_sede', true), 'EUA'); ?>>🇺🇸 EUA</option>
                        <option value="México" <?php selected(get_post_meta($post->ID, 'pais_sede', true), 'México'); ?>>🇲🇽 México</option>
                        <option value="Canadá" <?php selected(get_post_meta($post->ID, 'pais_sede', true), 'Canadá'); ?>>🇨🇦 Canadá</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:10px;">
                <div class="wp-admin-field" style="flex:1;">
                    <label>Grupo:</label>
                    <input type="text" name="grupo_copa" value="<?php echo esc_attr(get_post_meta($post->ID, 'grupo_copa', true)); ?>" placeholder="Ex: Grupo A">
                </div>
                <div class="wp-admin-field" style="flex:1;">
                    <label>Fase do Torneio:</label>
                    <select name="fase_copa">
                        <option value="Fase de Grupos" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Fase de Grupos'); ?>>Fase de Grupos</option>
                        <option value="Rodada de 32" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Rodada de 32'); ?>>Rodada de 32</option>
                        <option value="Oitavas de Final" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Oitavas de Final'); ?>>Oitavas de Final</option>
                        <option value="Quartas de Final" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Quartas de Final'); ?>>Quartas de Final</option>
                        <option value="Semifinal" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Semifinal'); ?>>Semifinal</option>
                        <option value="Final" <?php selected(get_post_meta($post->ID, 'fase_copa', true), 'Final'); ?>>Final</option>
                    </select>
                </div>
            </div>
            <div class="wp-admin-field">
                <label>ID da Partida (Ex: J73):</label>
                <input type="text" name="match_id" value="<?php echo esc_attr(get_post_meta($post->ID, 'match_id', true)); ?>" placeholder="Ex: J73">
            </div>
        </div>
    <?php endif; ?>

    <div class="wp-admin-field">
        <label>Time Casa (Nome):</label>
        <input type="text" name="time_casa" value="<?php echo esc_attr($time_casa); ?>" placeholder="Ex: Flamengo">
    </div>
    <div class="wp-admin-field">
        <label>URL Escudo Casa:</label>
        <div style="display:flex; gap:5px;">
            <input type="text" name="escudo_casa" id="escudo_casa" value="<?php echo esc_attr($escudo_casa); ?>" style="flex:1;">
            <button type="button" class="button select-img" data-target="#escudo_casa">Selecionar</button>
        </div>
    </div>
    <div class="wp-admin-field">
        <label>Time Fora (Nome):</label>
        <input type="text" name="time_fora" value="<?php echo esc_attr($time_fora); ?>" placeholder="Ex: Palmeiras">
    </div>
    <div class="wp-admin-field">
        <label>URL Escudo Fora:</label>
        <div style="display:flex; gap:5px;">
            <input type="text" name="escudo_fora" id="escudo_fora" value="<?php echo esc_attr($escudo_fora); ?>" style="flex:1;">
            <button type="button" class="button select-img" data-target="#escudo_fora">Selecionar</button>
        </div>
    </div>
    <div style="display:flex; gap:10px; background: #f0f0f0; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <div class="wp-admin-field" style="flex:1; margin-bottom: 0;">
            <label>Gols Casa:</label>
            <input type="number" name="placar_casa" value="<?php echo esc_attr($placar_casa); ?>" placeholder="0">
        </div>
        <div class="wp-admin-field" style="flex:1; margin-bottom: 0;">
            <label>Gols Fora:</label>
            <input type="number" name="placar_fora" value="<?php echo esc_attr($placar_fora); ?>" placeholder="0">
        </div>
        <div class="wp-admin-field" style="flex:1; margin-bottom: 0;">
            <label>Status:</label>
            <select name="status_jogo">
                <option value="Agendado" <?php selected($status_jogo, 'Agendado'); ?>>⏳ Agendado</option>
                <option value="Ao Vivo" <?php selected($status_jogo, 'Ao Vivo'); ?>>🔴 Ao Vivo</option>
                <option value="Encerrado" <?php selected($status_jogo, 'Encerrado'); ?>>🏁 Encerrado</option>
            </select>
        </div>
    </div>
    <div class="wp-admin-field">
        <label>Data do Jogo:</label>
        <input type="date" name="data_jogo" value="<?php echo esc_attr($data_jogo); ?>">
    </div>
    <div class="wp-admin-field">
        <label>Horário:</label>
        <input type="text" name="horario" value="<?php echo esc_attr($horario); ?>" placeholder="Ex: 16h00">
    </div>
    <div class="wp-admin-field">
        <label>Estádio / Local:</label>
        <input type="text" name="estadio" value="<?php echo esc_attr(get_post_meta($post->ID, 'estadio', true)); ?>" placeholder="Ex: Maracanã">
    </div>
    <div class="wp-admin-field">
        <label>Onde Assistir (Selecione um ou mais):</label>
        <div style="background: #fff; border: 1px solid #ccc; padding: 10px; max-height: 150px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 5px;">
            <?php
            $canais = get_terms(array('taxonomy' => 'canal', 'hide_empty' => false));
            foreach ($canais as $canal) :
                $checked = in_array($canal->term_id, $canais_selecionados) ? 'checked' : '';
            ?>
                <label style="font-weight: normal; margin-bottom: 0;">
                    <input type="checkbox" name="onde_assistir[]" value="<?php echo esc_attr($canal->term_id); ?>" <?php echo $checked; ?>>
                    <?php echo esc_html($canal->name); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="wp-admin-field">
        <label>Rodada (Opcional):</label>
        <input type="text" name="rodada" value="<?php echo esc_attr(get_post_meta($post->ID, 'rodada', true)); ?>" placeholder="Ex: 4ª Rodada">
    </div>

    <hr>
    <div class="wp-admin-field">
        <label>Link da Transmissão (Botão):</label>
        <input type="text" name="link_transmissao" value="<?php echo esc_attr($link_transmissao); ?>" placeholder="https://...">
    </div>
    <div style="display:flex; gap:10px;">
        <div class="wp-admin-field" style="flex:1;">
            <label>Odds Casa (Ex: 1.85):</label>
            <input type="text" name="oddCasa" value="<?php echo esc_attr($oddCasa); ?>" placeholder="1.85">
        </div>
        <div class="wp-admin-field" style="flex:1;">
            <label>Odds Empate (Ex: 3.20):</label>
            <input type="text" name="oddEmpate" value="<?php echo esc_attr($oddEmpate); ?>" placeholder="3.20">
        </div>
        <div class="wp-admin-field" style="flex:1;">
            <label>Odds Fora (Ex: 4.50):</label>
            <input type="text" name="oddFora" value="<?php echo esc_attr($oddFora); ?>" placeholder="4.50">
        </div>
    </div>
    <hr>
    <?php
}

/**
 * BOTÃO DE GERAR SEO NO TOPO DO EDITOR (Ao lado de Adicionar Mídia)
 */
function adicionar_botao_seo_editor() {
    $screen = get_current_screen();
    if ( $screen->post_type !== 'jogo' && $screen->post_type !== 'jogo_copa' ) return;
    
    echo '<button type="button" id="btn-gerar-ia" class="button" style="background: #e67e22; border-color: #d35400; color: #fff; font-weight: bold; margin-left: 5px;">
        <span class="dashicons dashicons-superhero" style="margin-top: 4px; font-size: 16px;"></span> ✨ Gerar Texto SEO
    </button>';
}
add_action('media_buttons', 'adicionar_botao_seo_editor');

/**
 * META BOXES PARA BARES
 */
function adicionar_meta_boxes_bares() {
    add_meta_box('dados_bar', 'Detalhes do Estabelecimento', 'renderizar_meta_box_bar', 'bar', 'normal', 'high');
}
add_action('add_meta_boxes', 'adicionar_meta_boxes_bares');

function renderizar_meta_box_bar($post) {
    // Adicionar Nonce para segurança
    wp_nonce_field('salvar_dados_bar', 'bar_nonce');

    $endereco = get_post_meta($post->ID, 'endereco', true);
    $horario_abertura = get_post_meta($post->ID, 'horario_abertura', true);
    $destaque_jogo = get_post_meta($post->ID, 'destaque_jogo', true);
    $contato = get_post_meta($post->ID, 'contato', true);
    ?>
    <div class="wp-admin-field">
        <label>Endereço Completo:</label>
        <input type="text" name="endereco" value="<?php echo esc_attr($endereco); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Status/Horário (Ex: Abre 18h):</label>
        <input type="text" name="horario_abertura" value="<?php echo esc_attr($horario_abertura); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Jogo em Destaque no Local:</label>
        <input type="text" name="destaque_jogo" value="<?php echo esc_attr($destaque_jogo); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Contato (Whats ou Insta):</label>
        <input type="text" name="contato" value="<?php echo esc_attr($contato); ?>" style="width:100%;">
    </div>
    <?php
}

/**
 * META BOXES PARA SELEÇÕES E JOGADORES
 */
function adicionar_meta_boxes_especificas() {
    add_meta_box('dados_selecao', 'Dados da Seleção', 'render_meta_box_selecao', 'selecao', 'normal', 'high');
    add_meta_box('dados_jogador', 'Dados do Jogador', 'render_meta_box_jogador', 'jogador', 'normal', 'high');
}
add_action('add_meta_boxes', 'adicionar_meta_boxes_especificas');

function render_meta_box_selecao($post) {
    $tecnico = get_post_meta($post->ID, 'tecnico', true);
    $ranking = get_post_meta($post->ID, 'ranking_fifa', true);
    $grupo = get_post_meta($post->ID, 'grupo_selecao', true);
    $cor = get_post_meta($post->ID, 'cor_primaria', true) ?: '#1e293b';
    $participacoes = get_post_meta($post->ID, 'participacoes', true);
    ?>
    <style>.wp-admin-field { margin-bottom: 15px; } .wp-admin-field label { display: block; font-weight: bold; margin-bottom: 5px; } .wp-admin-field input, .wp-admin-field select { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }</style>
    <div class="wp-admin-field">
        <label>Cor Predominante (Hexadecimal):</label>
        <input type="color" name="cor_primaria" value="<?php echo esc_attr($cor); ?>" style="height:40px;">
    </div>
    <div class="wp-admin-field">
        <label>Técnico:</label>
        <input type="text" name="tecnico" value="<?php echo esc_attr($tecnico); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Ranking FIFA:</label>
        <input type="number" name="ranking_fifa" value="<?php echo esc_attr($ranking); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Participações em Copas:</label>
        <input type="text" name="participacoes" value="<?php echo esc_attr($participacoes); ?>" placeholder="Ex: 22 participações" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Grupo na Copa:</label>
        <input type="text" name="grupo_selecao" value="<?php echo esc_attr($grupo); ?>" placeholder="Ex: Grupo A" style="width:100%;">
    </div>
    <?php
}

function render_meta_box_jogador($post) {
    $selecao_id = get_post_meta($post->ID, 'selecao_vinculada', true);
    $posicao = get_post_meta($post->ID, 'posicao', true);
    $numero = get_post_meta($post->ID, 'numero_camisa', true);
    $clube = get_post_meta($post->ID, 'clube_atual', true);
    ?>
    <div class="wp-admin-field">
        <label>Seleção:</label>
        <select name="selecao_vinculada" style="width:100%;">
            <option value="">Selecione a Seleção...</option>
            <?php
            $selecoes = get_posts(array('post_type' => 'selecao', 'posts_per_page' => -1));
            foreach ($selecoes as $sel) {
                echo '<option value="'.$sel->ID.'" '.selected($selecao_id, $sel->ID, false).'>'.$sel->post_title.'</option>';
            }
            ?>
        </select>
    </div>
    <div class="wp-admin-field">
        <label>Posição:</label>
        <input type="text" name="posicao" value="<?php echo esc_attr($posicao); ?>" placeholder="Ex: Atacante" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Número da Camisa:</label>
        <input type="number" name="numero_camisa" value="<?php echo esc_attr($numero); ?>" style="width:100%;">
    </div>
    <div class="wp-admin-field">
        <label>Clube Atual:</label>
        <input type="text" name="clube_atual" value="<?php echo esc_attr($clube); ?>" style="width:100%;">
    </div>
    <?php
}

function salvar_meta_boxes_assistir_jogos($post_id) {
    // Verificar Nonces
    if (!isset($_POST['jogo_nonce']) || !wp_verify_nonce($_POST['jogo_nonce'], 'salvar_dados_jogo')) {
        if (!isset($_POST['bar_nonce']) || !wp_verify_nonce($_POST['bar_nonce'], 'salvar_dados_bar')) {
            return;
        }
    }

    // Não salvar em auto-save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Verificar permissões
    if (!current_user_can('edit_post', $post_id)) return;

    // Campos de texto simples
    $campos_texto = array(
        'time_casa', 'time_fora', 'escudo_casa', 'escudo_fora', 'data_jogo', 'horario',
        'link_transmissao', 'oddCasa', 'oddEmpate', 'oddFora', 'estadio', 'rodada',
        'cidade_sede', 'pais_sede', 'grupo_copa', 'fase_copa', 'placar_casa', 'placar_fora', 'status_jogo',
        'time_casa_id', 'time_fora_id', 'match_id', 'fase_bracket'
    );

    foreach ($campos_texto as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    // Campo de análise (Permitir HTML seguro)
    if (isset($_POST['analise_jogo'])) {
        update_post_meta($post_id, 'analise_jogo', wp_kses_post($_POST['analise_jogo']));
    }

    // Campos de Bares
    $campos_bar = array('endereco', 'horario_abertura', 'destaque_jogo', 'contato');
    foreach ($campos_bar as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    // Campos de Seleção e Jogador
    $campos_extras = array('tecnico', 'ranking_fifa', 'grupo_selecao', 'selecao_vinculada', 'posicao', 'numero_camisa', 'clube_atual', 'cor_primaria', 'participacoes');
    foreach ($campos_extras as $campo) {
        if (isset($_POST[$campo])) {
            update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo]));
        }
    }

    // Sincronizar campo 'onde_assistir' com a taxonomia 'canal'
    if (isset($_POST['onde_assistir'])) {
        $canais_ids = array_map('intval', (array)$_POST['onde_assistir']);
        wp_set_object_terms($post_id, $canais_ids, 'canal');
        
        // Também salvar o primeiro canal no meta para compatibilidade legada se necessário
        if (!empty($canais_ids)) {
            update_post_meta($post_id, 'onde_assistir', $canais_ids[0]);
        } else {
            delete_post_meta($post_id, 'onde_assistir');
        }
    } else {
        wp_set_object_terms($post_id, array(), 'canal');
        delete_post_meta($post_id, 'onde_assistir');
    }
}
add_action('save_post', 'salvar_meta_boxes_assistir_jogos');

// Enqueue Scripts e Styles
function assistir_jogos_scripts() {
    // Tailwind e FontAwesome via CDN como no seu HTML
    wp_enqueue_script( 'tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false );
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null );
    
    // Nosso CSS customizado (se necessário)
    wp_enqueue_style( 'assistir-jogos-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'assistir_jogos_scripts' );

// Scripts específicos para o Admin (Biblioteca de Mídia)
function assistir_jogos_admin_scripts($hook) {
    $allowed_hooks = array('post.php', 'post-new.php', 'term.php', 'edit-tags.php');
    if (!in_array($hook, $allowed_hooks)) return;
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'assistir_jogos_admin_scripts');

// Injetar o Script no Footer para máxima compatibilidade
add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($){
        // SELETOR DE IMAGENS (Escudos e Logos)
        $(document).on('click', '.select-img', function(e) {
            e.preventDefault();
            var btn = $(this);
            var target = btn.data('target');
            var input = target ? $(target) : btn.closest('div, td').find('input[type="text"]');
            
            if (typeof wp !== 'undefined' && wp.media) {
                var frame = wp.media({
                    title: 'Selecionar Imagem',
                    button: { text: 'Usar Imagem' },
                    multiple: false
                }).on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.val(attachment.url).trigger('change');
                }).open();
            }
        });

        // GERADOR DE TEXTO SEO PROFISSIONAL
        $(document).on('click', '#btn-gerar-ia', function(e) {
            e.preventDefault();
            
            // 1. Capturar Dados
            var tCasa   = $('input[name="time_casa"]').val() || 'Time A';
            var tFora   = $('input[name="time_fora"]').val() || 'Time B';
            var dataRaw = $('input[name="data_jogo"]').val();
            var hora    = $('input[name="horario"]').val() || '--:--';
            var estadio = $('input[name="estadio"]').val() || 'Estádio';
            var rodada  = $('input[name="rodada"]').val() || 'Fase de Grupos';
            
            // Canais Selecionados
            var canais = [];
            $('input[name="onde_assistir[]"]:checked').each(function(){
                canais.push($(this).parent().text().trim());
            });
            var canaisTxt = canais.length > 0 ? canais.join(', ').replace(/, ([^,]*)$/, ' e $1') : 'emissoras oficiais';

            // Campeonato (Busca no checklist de taxonomia)
            var camp = $('#campeonatochecklist input:checked').first().parent().text().trim() || 'Futebol';

            // 2. Formatar Data
            var dataFormatada = 'Data Indisponível';
            if (dataRaw) {
                var dataObj = new Date(dataRaw + 'T12:00:00');
                var dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
                var meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                dataFormatada = dias[dataObj.getDay()] + ', ' + dataObj.getDate() + ' ' + meses[dataObj.getMonth()] + ' ' + dataObj.getFullYear();
            }

            // 3. Mapeamento de Direitos por Competição
            var direitosMap = {
                'Brasileirão': 'Globosat (Premiere, SporTV, Globo), Grupo Record (R7, TV Record), Amazon Prime Video, CazéTV e YouTube.',
                'Copa do Brasil': 'Globo, SporTV, Premiere e Amazon Prime Video.',
                'Libertadores': 'Globo, ESPN, Disney+ e Paramount+.',
                'Champions League': 'SBT, TNT Sports e Max.',
                'Premier League': 'ESPN e Disney+.'
            };
            var direitos = direitosMap[camp] || 'emissoras detentoras dos direitos oficiais.';

            // 4. Montar o Template HTML Final (Com as novas palavras do usuário)
            var html = `
<h2>Detalhes do Jogo</h2>
<p><strong>Jogo:</strong> ${tCasa} x ${tFora}</p>
<p><strong>Data:</strong> ${dataFormatada}</p>
<p><strong>Horário de início:</strong> ${hora}</p>
<p><strong>Rodada:</strong> ${rodada}</p>

<p>As escalações oficiais de ambas as equipes serão divulgadas cerca de uma hora antes do apito inicial.</p>
<p>Os resultados ao vivo, os lances do jogo e as estatísticas são atualizados em tempo real assim que a partida começa.</p>
<p>A cobertura da partida inclui listas confirmadas de canais de TV, opções de streaming e atualizações ao vivo.</p>
<p>Todas as emissoras listadas são detentoras oficiais dos direitos desta partida.</p>

<h3>Como assistir ${tCasa} x ${tFora} no Brasil</h3>
<p>No Brasil, o jogo será exibido ao vivo em: <strong>${canaisTxt}</strong>.</p>

<h3>Como assistir ${tCasa} x ${tFora} no resto do mundo</h3>
<p>Os torcedores podem assistir ao jogo ao vivo na TV e online através das emissoras oficiais. Use a grade de programação acima para encontrar a transmissão oficial ao vivo disponível na sua localização.</p>

<h3>Como assistir à competição ${camp}</h3>
<p>No território brasileiro, você pode assistir aos jogos da competição <strong>${camp}</strong> ao vivo por: <strong>${direitos}</strong></p>
<p><em>Mais detalhes: Como assistir ao ${camp} no Brasil.</em></p>

<hr />
<h3>Aviso Legal de Conteúdo</h3>
<p>Este website atua estritamente como um guia informativo de programação esportiva. As listas de jogos e eventos aqui publicadas referem-se exclusivamente aos portadores oficiais de direitos televisivos autorizados. As transmissões estão disponíveis em plataformas legítimas como TV aberta, cabo, satélite, e aplicativos oficiais. Nosso objetivo é facilitar o acesso do torcedor aos canais legais, providenciando links diretos para as plataformas dos transmissores oficiais sempre que disponível. Ressaltamos que o acesso a esses conteúdos pode exigir assinatura paga ou autenticação com um provedor de Internet/TV. <strong>Este site não hospeda, não transmite e não realiza a retransmissão de qualquer sinal audiovisual.</strong> Embora busquemos a máxima precisão, os horários de exibição são de inteira responsabilidade das emissoras e podem sofrer alterações sem aviso prévio. Se encontrar alguma informação incorreta, por favor, entre em contato conosco.</p>
            `;

            // 5. Injetar no Editor (Detecta Main Content ou Custom Field)
            var targetEditor = 'content'; // Alvo principal agora é o corpo do post
            
            if (typeof tinymce !== 'undefined' && tinymce.get(targetEditor)) {
                tinymce.get(targetEditor).setContent(html);
            } else {
                $('#' + targetEditor).val(html);
            }
        });
    });
    </script>
    <?php
});

/**
 * SEO AUTOMÁTICO - Títulos e Descrições
 */
function assistir_jogos_seo_tags() {
    if ( is_singular('jogo') ) {
        $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
        $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
        $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? 'Futebol';
        $horario = get_post_meta(get_the_ID(), 'horario', true);
        $data_jogo = get_post_meta(get_the_ID(), 'data_jogo', true);
        $data_formatada = $data_jogo ? date('d/m/Y', strtotime($data_jogo)) : date('d/m/Y');
        
        $desc = "Saiba onde assistir ao vivo o jogo {$time_casa} x {$time_fora} pela {$campeonato} em {$data_formatada}. Confira o horário ({$horario}), escalações e links de transmissão oficial.";
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
}
add_action('wp_head', 'assistir_jogos_seo_tags');

function assistir_jogos_custom_title( $title ) {
    if ( is_singular('jogo') ) {
        $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
        $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
        $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? 'Futebol';
        $data_raw = get_post_meta(get_the_ID(), 'data_jogo', true);
        $data_f = $data_raw ? date('d/m/Y', strtotime($data_raw)) : date('d/m/Y');
        
        return "Onde assistir {$time_casa} x {$time_fora} ao vivo - {$campeonato} ({$data_f})";
    }
    return $title;
}
add_filter( 'pre_get_document_title', 'assistir_jogos_custom_title', 10 );

/**
 * SCHEMA.ORG - Dados Estruturados para o Google
 */
function assistir_jogos_schema_event() {
    if ( is_singular('jogo') ) {
        $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
        $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
        $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? 'Futebol';
        $horario = get_post_meta(get_the_ID(), 'horario', true);
        $logo_casa = get_post_meta(get_the_ID(), 'escudo_casa', true);
        $logo_fora = get_post_meta(get_the_ID(), 'escudo_fora', true);
        $estadio = get_post_meta(get_the_ID(), 'estadio', true);
        
        $data_meta = get_post_meta(get_the_ID(), 'data_jogo', true) ?: get_the_date('Y-m-d');
        $date_iso = $data_meta . 'T' . str_replace('h', ':', $horario) . ':00-03:00';

        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "SportsEvent",
            "name" => "{$time_casa} x {$time_fora}",
            "startDate" => $date_iso,
            "description" => "Onde assistir {$time_casa} x {$time_fora} ao vivo pela {$campeonato}.",
            "homeTeam" => array("@type" => "SportsTeam", "name" => $time_casa, "logo" => $logo_casa),
            "awayTeam" => array("@type" => "SportsTeam", "name" => $time_fora, "logo" => $logo_fora),
            "sport" => "Soccer",
            "location" => array(
                "@type" => "Place",
                "name" => $estadio ?: "Estádio Esportivo",
                "address" => array("@type" => "PostalAddress", "addressLocality" => "Brasil")
            )
        );
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }

    if ( is_singular('post') ) {
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "NewsArticle",
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id" => get_permalink()
            ),
            "headline" => get_the_title(),
            "image" => array(get_the_post_thumbnail_url(get_the_ID(), 'full')),
            "datePublished" => get_the_date('c'),
            "dateModified" => get_the_modified_date('c'),
            "author" => array(
                "@type" => "Person",
                "name" => get_the_author(),
                "url" => get_author_posts_url(get_the_author_meta('ID'))
            ),
            "publisher" => array(
                "@type" => "Organization",
                "name" => "Assistir Jogos",
                "logo" => array(
                    "@type" => "ImageObject",
                    "url" => get_template_directory_uri() . "/assets/images/logotipo_assistir_jogos.webp"
                )
            ),
            "description" => wp_strip_all_tags(get_the_excerpt()) ?: wp_trim_words(get_the_content(), 25)
        );
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }
}
add_action('wp_head', 'assistir_jogos_schema_event');

// ==========================================
// SISTEMA DE ATUALIZAÇÃO VIA GITHUB
// ==========================================
function check_for_theme_update($transient) {
    if (empty($transient->checked)) return $transient;

    $theme_slug = 'themeassistirjogos';
    $repo = 'JucaBonini/sts_assistir_jogos';
    
    // Busca informações da release mais recente via GitHub API
    $response = wp_remote_get("https://api.github.com/repos/$repo/releases/latest");
    
    if (is_wp_error($response)) return $transient;

    $data = json_decode(wp_remote_retrieve_body($response));
    if (!isset($data->tag_name)) return $transient;

    $new_version = str_replace('v', '', $data->tag_name);
    $current_version = wp_get_theme()->get('Version');

    if (version_compare($current_version, $new_version, '<')) {
        $transient->response[$theme_slug] = array(
            'theme'       => $theme_slug,
            'new_version' => $new_version,
            'url'         => "https://github.com/$repo",
            'package'     => $data->zipball_url,
        );
    }

    return $transient;
}
add_filter('site_transient_update_themes', 'check_for_theme_update');

/**
 * OTIMIZAÇÃO DE IMAGENS (WebP, Discover e Auto-Alt)
 */

// 1. Forçar conversão de novos uploads para WebP e definir qualidade
function otimizar_qualidade_imagem($quality) {
    return 75; // Otimizado para performance máxima
}
add_filter('jpeg_quality', 'otimizar_qualidade_imagem');
add_filter('wp_editor_set_quality', 'otimizar_qualidade_imagem');
add_filter('webp_uploads_upload_image_quality', 'otimizar_qualidade_imagem');

// 2. Adicionar tamanho padrão para Google Discover (1200x675)
add_image_size('google_discover', 1200, 675, true);

// 3. Preenchimento automático de texto ALT baseado no título do post
function auto_set_image_alt_from_title($attachment_ID) {
    $parent_id = wp_get_post_parent_id($attachment_ID);
    if ($parent_id) {
        $post_title = get_the_title($parent_id);
        if ($post_title) {
            update_post_meta($attachment_ID, '_wp_attachment_image_alt', $post_title);
        }
    }
}
add_action('add_attachment', 'auto_set_image_alt_from_title');

// 4. Forçar WordPress a preferir WebP se disponível no servidor
function converter_upload_para_webp($upload) {
    if ($upload['type'] == 'image/jpeg' || $upload['type'] == 'image/png') {
        // WordPress 5.8+ já lida bem com isso, mas aqui garantimos a compressão.
    }
    return $upload;
}
add_filter('wp_handle_upload', 'converter_upload_para_webp');

/**
 * SEO ENGINE - SEM PLUGINS
 */

// 1. Limpeza do Sitemap Nativo (Manter apenas o essencial)
add_filter('wp_sitemaps_post_types', function($post_types) {
    // Removemos o que não queremos no sitemap
    unset($post_types['page']); // Se quiser remover páginas
    return $post_types;
});

add_filter('wp_sitemaps_add_provider', function($provider, $name) {
    // Removemos sitemaps de usuários e tags para focar no conteúdo principal
    if (in_array($name, ['users', 'taxonomies'])) {
        return false;
    }
    return $provider;
}, 10, 2);

// 2. Personalização do Título (SEO Title)
function custom_seo_title($title) {
    if (is_front_page()) {
        return "Assistir Jogos - Onde assistir futebol e esportes ao vivo hoje";
    }
    if (is_singular('post')) {
        return get_the_title() . " | Notícias - Assistir Jogos";
    }
    return $title;
}
add_filter('pre_get_document_title', 'custom_seo_title', 20);

// 3. URLs Amigáveis (Garantir que o slug seja limpo)
add_filter('editable_slug', function($slug) {
    return strtolower($slug);
});

/**
 * BREADCRUMBS CUSTOMIZADOS (SEO & UX)
 */
function custom_breadcrumbs() {
    if (is_front_page()) return;

    echo '<nav class="container mx-auto px-4 py-3 text-xs text-gray-400 flex items-center space-x-2 overflow-x-auto whitespace-nowrap" aria-label="Breadcrumb">';
    echo '<a href="' . home_url() . '" class="hover:text-laranja transition"><i class="fas fa-home"></i> Início</a>';
    
    if (is_category() || is_single()) {
        echo '<span class="text-gray-600">/</span>';
        if (is_singular('jogo')) {
            echo '<a href="' . home_url('#jogos-hoje') . '" class="hover:text-laranja transition">Jogos</a>';
        } else {
            $categories = get_the_category();
            if ($categories) {
                echo '<a href="' . get_category_link($categories[0]->term_id) . '" class="hover:text-laranja transition">' . $categories[0]->name . '</a>';
            }
        }
        
        if (is_single()) {
            echo '<span class="text-gray-600">/</span>';
            echo '<span class="text-gray-300 truncate max-w-[200px]">' . get_the_title() . '</span>';
        }
    } elseif (is_archive()) {
        echo '<span class="text-gray-600">/</span>';
        echo '<span class="text-gray-300">' . get_the_archive_title() . '</span>';
    } elseif (is_page()) {
        echo '<span class="text-gray-600">/</span>';
        echo '<span class="text-gray-300">' . get_the_title() . '</span>';
    }

    echo '</nav>';
}

/**
 * REDIRECIONAMENTO INTELIGENTE (404 para Home)
 */
function redirect_404_to_home() {
    if (is_404()) {
        wp_redirect(home_url(), 301);
        exit;
    }
}
add_action('template_redirect', 'redirect_404_to_home');

/**
 * PROTEÇÃO ANTI-PLÁGIO (RSS Feed)
 */
function protect_rss_content($content) {
    if (is_feed()) {
        $post_link = get_permalink();
        $site_link = home_url();
        $content .= "<hr><p>O post <a href='$post_link'>" . get_the_title() . "</a> apareceu primeiro em <a href='$site_link'>Assistir Jogos</a>.</p>";
    }
    return $content;
}
add_filter('the_excerpt_rss', 'protect_rss_content');
add_filter('the_content_feed', 'protect_rss_content');

/**
 * 1. FAQ SCHEMA AUTOMÁTICO (Jogos)
 */
function assistir_jogos_faq_schema() {
    if (is_singular('jogo')) {
        $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
        $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
        $horario = get_post_meta(get_the_ID(), 'horario', true);
        $canal = get_post_meta(get_the_ID(), 'onde_assistir', true);
        $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? 'Futebol';

        $faq = array(
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => array(
                array(
                    "@type" => "Question",
                    "name" => "Onde assistir ao vivo o jogo {$time_casa} x {$time_fora}?",
                    "acceptedAnswer" => array(
                        "@type" => "Answer",
                        "text" => "Você pode assistir {$time_casa} x {$time_fora} ao vivo no canal {$canal} pela {$campeonato}."
                    )
                ),
                array(
                    "@type" => "Question",
                    "name" => "Qual o horário do jogo {$time_casa} x {$time_fora} hoje?",
                    "acceptedAnswer" => array(
                        "@type" => "Answer",
                        "text" => "A partida entre {$time_casa} e {$time_fora} está programada para começar às {$horario}."
                    )
                )
            )
        );
        echo '<script type="application/ld+json">' . json_encode($faq) . '</script>' . "\n";
    }
}
add_action('wp_head', 'assistir_jogos_faq_schema');

/**
 * 2. REMOÇÃO DE BLOAT (Velocidade Extrema)
 */
function limpar_wp_bloat() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'wp_generator'); // Segurança
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
}
add_action('init', 'limpar_wp_bloat');

/**
 * 3. OTIMIZAÇÃO DE ROBOTS.TXT
 */
function otimizar_robots_txt($output, $public) {
    $output .= "Disallow: /wp-admin/\n";
    $output .= "Disallow: /wp-includes/\n";
    $output .= "Allow: /wp-admin/admin-ajax.php\n";
    $output .= "Sitemap: " . home_url('/wp-sitemap.xml') . "\n";
    return $output;
}
add_filter('robots_txt', 'otimizar_robots_txt', 10, 2);

/**
 * 1. CAMPOS DE SEO NO CUSTOMIZER (Analytics/Search Console)
 */
function customizer_seo_settings($wp_customize) {
    $wp_customize->add_section('seo_settings', array('title' => 'Configurações de SEO', 'priority' => 30));
    
    // Google Analytics
    $wp_customize->add_setting('ga_id', array('default' => '', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('ga_id_control', array('label' => 'ID do Google Analytics (G-XXXXX)', 'section' => 'seo_settings', 'settings' => 'ga_id'));

    // Search Console
    $wp_customize->add_setting('gsc_id', array('default' => '', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('gsc_id_control', array('label' => 'Tag de Verificação Search Console', 'section' => 'seo_settings', 'settings' => 'gsc_id'));
}
add_action('customize_register', 'customizer_seo_settings');

// Injetar scripts no head
function inject_seo_scripts() {
    $ga_id = get_theme_mod('ga_id');
    $gsc_id = get_theme_mod('gsc_id');
    
    if ($gsc_id) echo '<meta name="google-site-verification" content="' . esc_attr($gsc_id) . '" />' . "\n";
    if ($ga_id) : ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '<?php echo esc_attr($ga_id); ?>');</script>
    <?php endif; ?>
    
    <!-- PWA Zeus -->
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">
    <meta name="theme-color" content="#f97316">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
                    .then(reg => console.log('Zeus PWA Ativo!', reg))
                    .catch(err => console.log('PWA falhou:', err));
            });
        }
    </script>
<?php
}
add_action('wp_head', 'inject_seo_scripts');

/**
 * 2. SEO CHECKLIST META BOX
 */
function add_seo_checklist_metabox() {
    add_meta_box('seo_checklist', '✅ Zeus SEO Checklist', 'render_seo_checklist', array('post', 'jogo', 'jogo_copa'), 'side', 'high');
}
add_action('add_meta_boxes', 'add_seo_checklist_metabox');

function render_seo_checklist() {
    echo '<ul style="margin:0; padding:0; list-style:none;">';
    echo '<li><input type="checkbox"> Palavra-chave no Título</li>';
    echo '<li><input type="checkbox"> Texto com +300 palavras</li>';
    echo '<li><input type="checkbox"> Imagem de destaque definida</li>';
    echo '<li><input type="checkbox"> Categorias selecionadas</li>';
    echo '<li><input type="checkbox"> Slug curto e amigável</li>';
    echo '</ul><p style="font-size:11px; color:#666; margin-top:10px;">Lembre-se: O SEO perfeito começa no conteúdo!</p>';
}

/**
 * 3. LOGICA DE POSTS RELACIONADOS
 */
function display_related_posts() {
    $post_id = get_the_ID();
    $categories = wp_get_post_categories($post_id);
    
    if ($categories) {
        $args = array(
            'category__in' => $categories,
            'post__not_in' => array($post_id),
            'posts_per_page' => 3,
            'post_type' => get_post_type($post_id)
        );
        $related_query = new WP_Query($args);
        
        if ($related_query->have_posts()) : ?>
            <div class="mt-12 pt-8 border-t border-slate-700">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-laranja">
                    <i class="fas fa-plus-circle"></i> Veja também
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        <a href="<?php the_permalink(); ?>" class="group block bg-slate-800 rounded-xl overflow-hidden card-hover">
                            <div class="aspect-video overflow-hidden">
                                <?php get_asna_thumbnail('medium', 'w-full h-full object-cover group-hover:scale-105 transition duration-500'); ?>
                            </div>
                            <div class="p-4">
                                <h4 class="font-medium text-sm line-clamp-2 group-hover:text-laranja transition"><?php the_title(); ?></h4>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif;
        wp_reset_postdata();
    }
}

/**
 * FUNÇÃO MESTRE PARA MINIATURAS (Com Fallback Inteligente)
 */
function get_asna_thumbnail($size = 'full', $class = '') {
    if (has_post_thumbnail()) {
        the_post_thumbnail($size, array('class' => $class));
    } else {
        $fallback_url = get_template_directory_uri() . '/assets/images/imagens-post.webp';
        echo '<img src="' . esc_url($fallback_url) . '" class="' . esc_attr($class) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
    }
}

/**
 * CONTEÚDO PADRÃO PARA NOVOS JOGOS
 */
function set_default_jogo_content($content, $post) {
    if ( $post->post_type === 'jogo' || $post->post_type === 'jogo_copa' ) {
        if ( empty($content) ) {
            $content = "<h2>Detalhes do Jogo</h2>\n<p>Jogo: [TIME_CASA] x [TIME_FORA]</p>\n<p>Data: [DATA_JOGO]</p>\n<p>Horário de início: [HORARIO]</p>\n<p>Rodada: [RODADA]</p>\n\n<p>As escalações oficiais de ambas as equipes serão divulgadas cerca de uma hora antes do apito inicial.</p>\n<p>Os resultados ao vivo, os lances do jogo e as estatísticas são atualizados em tempo real assim que a partida começa.</p>\n<p>A cobertura da partida inclui listas confirmadas de canais de TV, opções de streaming e atualizações ao vivo.</p>\n<p>Todas as emissoras listadas são detentoras oficiais dos direitos desta partida.</p>\n\n<h3>Como assistir [TIME_CASA] x [TIME_FORA] no Brasil</h3>\n<p>No Brasil, o jogo será exibido ao vivo em: [CANAIS_BRASIL]</p>\n\n<h3>Como assistir [TIME_CASA] x [TIME_FORA] no resto do mundo</h3>\n<p>Os torcedores podem assistir ao jogo ao vivo na TV e online através das emissoras oficiais. Use a grade de programação acima para encontrar a transmissão oficial ao vivo disponível na sua localização.</p>\n\n<h3>Como assistir à competição [CAMPEONATO]</h3>\n<p>No território brasileiro, você pode assistir aos jogos da competição [CAMPEONATO] ao vivo por: [DIREITOS_TV]</p>\n<p><em>Mais detalhes: Como assistir ao [CAMPEONATO] no Brasil.</em></p>\n\n<hr />\n<h3>Aviso Legal de Conteúdo</h3>\n<p>Este website atua estritamente como um guia informativo de programação esportiva. As listas de jogos e eventos aqui publicadas referem-se exclusivamente aos portadores oficiais de direitos televisivos autorizados. As transmissões estão disponíveis em plataformas legítimas como TV aberta, cabo, satélite, e aplicativos oficiais. Nosso objetivo é facilitar o acesso do torcedor aos canais legais, providenciando links diretos para as plataformas dos transmissores oficiais sempre que disponível. Ressaltamos que o acesso a esses conteúdos pode exigir assinatura paga ou autenticação com um provedor de Internet/TV. <strong>Este site não hospeda, não transmite e não realiza a retransmissão de qualquer sinal audiovisual.</strong> Embora busquemos a máxima precisão, os horários de exibição são de inteira responsabilidade das emissoras e podem sofrer alterações sem aviso prévio. Se encontrar alguma informação incorreta, por favor, entre em contato conosco.</p>";
        }
    }
    return $content;
}
add_filter('default_content', 'set_default_jogo_content', 10, 2);

// Incluir integrador da API-Football
require_once get_template_directory() . '/inc/api-football.php';


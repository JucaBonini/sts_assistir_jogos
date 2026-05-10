<?php get_header(); ?>

<main class="container mx-auto px-4 py-8">
    <header class="mb-12 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
            <?php the_archive_title(); ?>
        </h1>
        <div class="w-20 h-1 bg-laranja mx-auto rounded-full mb-6"></div>

        <?php 
        // Lógica específica para Canais
        if (is_tax('canal')) : 
            $term = get_queried_object();
            $logo = get_term_meta($term->term_id, 'logo_url', true);
            $link = get_term_meta($term->term_id, 'afiliado_url', true);
        ?>
            <div class="flex flex-col items-center mb-10">
                <?php if ($logo) : ?>
                    <img src="<?php echo esc_url($logo); ?>" class="h-20 object-contain mb-4 bg-white/10 p-2 rounded-xl">
                <?php endif; ?>
                
                <?php if ($link) : ?>
                    <a href="<?php echo esc_url($link); ?>" target="_blank" class="bg-verde-menta hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i> Assinar <?php echo esc_html($term->name); ?> Agora
                    </a>
                    <p class="text-[10px] text-gray-500 mt-2 italic">Apoie nosso guia assinando pelo link oficial acima.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (term_description()) : ?>
            <div class="max-w-3xl mx-auto text-gray-400 text-sm md:text-base leading-relaxed mb-10">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
    </header>

    <!-- CARDS DOS JOGOS (Query Direta do Arquivo) -->
    <section id="jogos-arquivo">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (have_posts()) : 
                $jogos_data = array();
                while (have_posts()) : the_post(); 
                    // Coletando dados para o JS (reutilizando a lógica)
                    $time_casa = get_post_meta(get_the_ID(), 'time_casa', true) ?: get_the_title();
                    $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
                    $escudo_casa = get_post_meta(get_the_ID(), 'escudo_casa', true);
                    $escudo_fora = get_post_meta(get_the_ID(), 'escudo_fora', true);
                    $horario = get_post_meta(get_the_ID(), 'horario', true);
                    $onde = get_post_meta(get_the_ID(), 'onde_assistir', true);
                    $selo = get_post_meta(get_the_ID(), 'tipo_acesso', true);
                    $tipo = wp_get_post_terms(get_the_ID(), 'esporte', array('fields' => 'slugs'))[0] ?? 'esporte';
                    $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? '';
                    
                    $jogos_data[] = array(
                        'timeCasa' => $time_casa,
                        'timeFora' => $time_fora,
                        'escudoCasa' => $escudo_casa,
                        'escudoFora' => $escudo_fora,
                        'horario' => $horario,
                        'onde' => $onde,
                        'selo' => $selo,
                        'tipo' => $tipo,
                        'campeonato' => $campeonato,
                        'link' => get_the_permalink()
                    );
                endwhile; ?>
                
                <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 col-span-full">
                    <!-- O JS renderizará aqui para manter a consistência e filtros -->
                </div>

                <script>
                    window.jogosData = <?php echo json_encode($jogos_data); ?>;
                </script>

            <?php else : ?>
                <div class="col-span-full text-center py-20 text-gray-500">
                    <i class="fas fa-search text-5xl mb-4 opacity-20"></i>
                    <p class="text-xl">Nenhum jogo encontrado para esta categoria no momento.</p>
                    <a href="<?php echo home_url(); ?>" class="text-laranja underline mt-4 inline-block">Voltar para a Home</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

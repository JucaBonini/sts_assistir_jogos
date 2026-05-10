<?php get_header(); ?>

<main class="container mx-auto px-4 py-12">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="max-w-5xl mx-auto">
            <!-- Cabeçalho da Notícia -->
            <header class="mb-10 text-center">
                <div class="flex justify-center items-center gap-4 mb-4">
                    <span class="bg-laranja text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Esportes</span>
                    <span class="text-gray-500 text-xs uppercase"><?php echo get_the_date(); ?> • Por Equipe Assistir Jogos</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white leading-tight mb-6">
                    <?php the_title(); ?>
                </h1>
            </header>

            <div class="rounded-3xl overflow-hidden shadow-2xl mb-12 border border-slate-700">
                <?php get_asna_thumbnail('google_discover', 'w-full h-auto object-cover'); ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <!-- Conteúdo Principal -->
                <div class="lg:col-span-8">
                    <div class="prose prose-invert prose-orange max-w-none text-gray-300 leading-relaxed text-lg">
                        <!-- ESPAÇO PARA ANÚNCIO ADSENSE TOPO -->
                        <div class="bg-slate-800/50 border border-dashed border-slate-700 p-4 mb-8 text-center text-[10px] text-gray-600 uppercase">Espaço Reservado para Anúncio AdSense</div>

                        <?php the_content(); ?>

                        <!-- ESPAÇO PARA ANÚNCIO ADSENSE RODAPÉ -->
                        <div class="bg-slate-800/50 border border-dashed border-slate-700 p-4 mt-8 text-center text-[10px] text-gray-600 uppercase">Espaço Reservado para Anúncio AdSense</div>
                    </div>

                    <!-- Rodapé do Artigo -->
                    <footer class="mt-12 pt-8 border-t border-slate-800">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 text-sm">Compartilhe:</span>
                                <a href="https://api.whatsapp.com/send?text=<?php the_title(); ?> - <?php the_permalink(); ?>" target="_blank" class="w-10 h-10 bg-green-600/20 text-green-500 rounded-full flex items-center justify-center hover:bg-green-600 hover:text-white transition-all"><i class="fab fa-whatsapp"></i></a>
                                <a href="https://twitter.com/intent/tweet?text=<?php the_title(); ?>&url=<?php the_permalink(); ?>" target="_blank" class="w-10 h-10 bg-blue-600/20 text-blue-400 rounded-full flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><i class="fab fa-twitter"></i></a>
                            </div>
                            <div class="text-sm text-gray-500 italic">Atualizado em: <?php the_modified_date(); ?></div>
                        </div>
                    </footer>
                </div>

                <!-- Sidebar (Lateral) -->
                <aside class="lg:col-span-4 space-y-8">
                    <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 sticky top-24">
                        <h3 class="text-white font-bold mb-4 flex items-center gap-2 uppercase text-sm tracking-widest">
                            <i class="fas fa-play-circle text-laranja"></i> Próximos Jogos
                        </h3>
                        <div class="space-y-4">
                            <?php
                            $jogos_sidebar = new WP_Query(array('post_type' => 'jogo', 'posts_per_page' => 5));
                            if ($jogos_sidebar->have_posts()) : while ($jogos_sidebar->have_posts()) : $jogos_sidebar->the_post();
                                $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
                                $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
                                $horario = get_post_meta(get_the_ID(), 'horario', true);
                            ?>
                                <a href="<?php the_permalink(); ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-900/50 hover:bg-slate-700 transition border border-transparent hover:border-laranja/30">
                                    <span class="text-xs text-white font-semibold truncate max-w-[120px]"><?php echo $time_casa; ?> x <?php echo $time_fora; ?></span>
                                    <span class="text-[10px] bg-laranja/10 text-laranja px-2 py-1 rounded font-bold"><?php echo $horario; ?></span>
                                </a>
                            <?php endwhile; wp_reset_postdata(); endif; ?>
                        </div>
                        <a href="<?php echo home_url(); ?>" class="block text-center mt-6 text-xs text-laranja hover:underline font-bold uppercase tracking-widest">Ver programação completa</a>
                    </div>
                </aside>
            </div>
        </article>
        <?php if (function_exists('display_related_posts')) display_related_posts(); ?>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>

<?php get_header(); ?>

<main class="container mx-auto px-4 py-12">
    <header class="mb-12">
        <h1 class="text-4xl font-black text-white mb-4 border-l-8 border-laranja pl-6 uppercase tracking-tighter">
            Notícias e <span class="text-laranja">Artigos</span>
        </h1>
        <p class="text-gray-400 max-w-2xl">Fique por dentro das últimas novidades do mundo esportivo, análises de jogos e guias de transmissão.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article class="bg-slate-800 rounded-3xl overflow-hidden shadow-2xl border border-slate-700 flex flex-col group hover:border-laranja/50 transition-all duration-300">
                <div class="aspect-video overflow-hidden">
                    <?php get_asna_thumbnail('medium_large', 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500'); ?>
                </div>

                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="text-[10px] font-bold text-laranja uppercase tracking-widest bg-laranja/10 px-2 py-1 rounded">Notícia</span>
                        <span class="text-[10px] text-gray-500 uppercase"><i class="far fa-calendar-alt mr-1"></i> <?php echo get_the_date(); ?></span>
                    </div>
                    
                    <h2 class="text-xl font-bold text-white mb-3 group-hover:text-laranja transition-colors">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    
                    <div class="text-gray-400 text-sm line-clamp-3 mb-6">
                        <?php the_excerpt(); ?>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-700/50">
                        <a href="<?php the_permalink(); ?>" class="text-sm font-bold text-white flex items-center gap-2 group-hover:translate-x-2 transition-transform">
                            Ler artigo completo <i class="fas fa-arrow-right text-laranja"></i>
                        </a>
                    </div>
                </div>
            </article>
        <?php endwhile; else : ?>
            <p class="text-gray-500 col-span-full text-center py-20">Nenhuma notícia publicada ainda.</p>
        <?php endif; ?>
    </div>

    <div class="mt-12">
        <?php the_posts_pagination(array(
            'mid_size'  => 2,
            'prev_text' => '<i class="fas fa-chevron-left"></i>',
            'next_text' => '<i class="fas fa-chevron-right"></i>',
            'class'     => 'pagination-custom'
        )); ?>
    </div>
</main>

<?php get_footer(); ?>

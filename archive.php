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
                    
                    // BUSCAR NOMES DOS CANAIS (Corrigindo o problema do ID)
                    $canais_nomes = wp_get_post_terms(get_the_ID(), 'canal', array('fields' => 'names'));
                    $onde = !empty($canais_nomes) ? implode(', ', $canais_nomes) : 'A definir';
                    
                    $tipo = wp_get_post_terms(get_the_ID(), 'esporte', array('fields' => 'slugs'))[0] ?? 'futebol';
                    $campeonato = wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? '';
                    $data_jogo = get_post_meta(get_the_ID(), 'data_jogo', true) ?: get_the_date('Y-m-d');
                    
                    $jogos_data[] = array(
                        'timeCasa' => $time_casa,
                        'timeFora' => $time_fora,
                        'escudoCasa' => $escudo_casa,
                        'escudoFora' => $escudo_fora,
                        'horario' => $horario,
                        'data' => $data_jogo,
                        'onde' => $onde,
                        'tipo' => $tipo,
                        'campeonato' => $campeonato,
                        'link' => get_the_permalink(),
                        'oddCasa' => get_post_meta(get_the_ID(), 'oddCasa', true) ?: '-',
                        'oddEmpate' => get_post_meta(get_the_ID(), 'oddEmpate', true) ?: '-',
                        'oddFora' => get_post_meta(get_the_ID(), 'oddFora', true) ?: '-',
                        'selo' => ''
                    );
                endwhile; ?>
                
                <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 col-span-full">
                    <!-- O JS renderizará aqui para manter a consistência e filtros -->
                </div>

                <script>
                    window.jogosData = <?php echo json_encode($jogos_data); ?>;

                    function renderJogos() {
                        const container = document.getElementById('cards-container');
                        if (!container || !window.jogosData) return;

                        const agora = new Date();
                        const timeFav = localStorage.getItem('time_favorito') ? localStorage.getItem('time_favorito').toLowerCase().trim() : null;

                        container.innerHTML = window.jogosData.map(jogo => {
                            const isFav = timeFav && (jogo.timeCasa.toLowerCase().includes(timeFav) || jogo.timeFora.toLowerCase().includes(timeFav));
                            
                            // Lógica de badge AO VIVO
                            let statusBadge = '';
                            if (jogo.status === 'Ao Vivo') {
                                statusBadge = '<span class="absolute top-3 right-3 bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded animate-pulse z-20 shadow-lg shadow-red-600/50 flex items-center gap-1"><i class="fas fa-circle text-[6px]"></i> AO VIVO</span>';
                            } else if (jogo.horario && jogo.data) {
                                const [h, m] = jogo.horario.replace('h', ':').split(':');
                                const [ano, mes, dia] = jogo.data.split('-');
                                const d = new Date(ano, mes - 1, dia, h, m, 0);
                                const diff = (agora - d) / 60000;
                                if (diff >= -5 && diff <= 120) {
                                    statusBadge = '<span class="absolute top-3 right-3 bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded animate-pulse z-20 shadow-lg shadow-red-600/50 flex items-center gap-1"><i class="fas fa-circle text-[6px]"></i> AO VIVO</span>';
                                }
                            }

                            return `
                                <div class="bg-slate-800/40 rounded-3xl p-6 border border-slate-700 hover:border-laranja/30 transition-all group relative flex flex-col h-full">
                                    ${statusBadge}
                                    <div class="flex justify-between items-start mb-6">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="bg-slate-700/50 text-gray-400 text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-wider">${jogo.tipo}</span>
                                            <span class="bg-laranja/10 text-laranja text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-wider flex items-center gap-1">
                                                <i class="fas fa-trophy text-[8px]"></i> ${jogo.campeonato}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 mb-8">
                                        <div class="flex-1 flex flex-col items-center text-center">
                                            <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center p-3 mb-3 border border-slate-700 group-hover:border-laranja/50 transition-colors">
                                                <img src="${jogo.escudoCasa}" alt="${jogo.timeCasa}" class="w-full h-full object-contain" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/logtipo_2.webp'">
                                            </div>
                                            <span class="text-xs font-bold text-white uppercase tracking-tighter line-clamp-1">${jogo.timeCasa}</span>
                                        </div>

                                        <div class="flex flex-col items-center">
                                            <div class="bg-slate-900 px-4 py-2 rounded-xl border border-slate-700 shadow-inner">
                                                <span class="text-laranja font-black text-lg tabular-nums tracking-widest">${jogo.horario}</span>
                                            </div>
                                            <span class="text-[9px] text-gray-500 font-bold uppercase mt-2">Hoje</span>
                                        </div>

                                        <div class="flex-1 flex flex-col items-center text-center">
                                            <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center p-3 mb-3 border border-slate-700 group-hover:border-laranja/50 transition-colors">
                                                <img src="${jogo.escudoFora}" alt="${jogo.timeFora}" class="w-full h-full object-contain" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/logtipo_2.webp'">
                                            </div>
                                            <span class="text-xs font-bold text-white uppercase tracking-tighter line-clamp-1">${jogo.timeFora}</span>
                                        </div>
                                    </div>

                                    <div class="mt-auto space-y-4">
                                        <div class="flex items-center justify-between text-[10px] text-gray-400 font-medium px-1">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-tv text-laranja"></i>
                                                <span class="truncate max-w-[140px]">${jogo.onde}</span>
                                            </div>
                                            <div class="flex gap-1">
                                                <span class="text-laranja font-black">${jogo.oddCasa}</span>
                                                <span class="text-gray-600">|</span>
                                                <span class="text-white font-black">${jogo.oddEmpate}</span>
                                                <span class="text-gray-600">|</span>
                                                <span class="text-laranja font-black">${jogo.oddFora}</span>
                                            </div>
                                        </div>
                                        
                                        <a href="${jogo.link}" class="w-full bg-slate-700 hover:bg-laranja text-white text-[11px] font-black py-3 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 group/btn shadow-lg hover:shadow-laranja/20 uppercase">
                                            Assistir Agora
                                            <i class="fas fa-play text-[8px] group-hover/btn:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }

                    document.addEventListener('DOMContentLoaded', renderJogos);
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

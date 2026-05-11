<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); 
    $time_casa = get_post_meta(get_the_ID(), 'time_casa', true);
    $time_fora = get_post_meta(get_the_ID(), 'time_fora', true);
    $horario = get_post_meta(get_the_ID(), 'horario', true);
    $canais_nomes = wp_get_post_terms(get_the_ID(), 'canal', array('fields' => 'names'));
    $onde = !empty($canais_nomes) ? implode(', ', $canais_nomes) : 'A definir';
    $categoria = get_post_meta(get_the_ID(), 'categoria', true);
    $categoria = get_post_meta(get_the_ID(), 'categoria', true);
    $escudo_casa = get_post_meta(get_the_ID(), 'escudo_casa', true);
    $escudo_fora = get_post_meta(get_the_ID(), 'escudo_fora', true);
    $analise = get_post_meta(get_the_ID(), 'analise_jogo', true);
    $estadio = get_post_meta(get_the_ID(), 'estadio', true);
    $rodada = get_post_meta(get_the_ID(), 'rodada', true);
    // Novos campos que sugerimos
    $link_transmissao = get_post_meta(get_the_ID(), 'link_transmissao', true) ?: '#';
    $probabilidade_casa = get_post_meta(get_the_ID(), 'oddCasa', true) ?: '2.10';
    $probabilidade_empate = get_post_meta(get_the_ID(), 'oddEmpate', true) ?: '3.40';
    $probabilidade_fora = get_post_meta(get_the_ID(), 'oddFora', true) ?: '4.20';
    
    // Gerar Link do Google Agenda
    $data_meta = get_post_meta(get_the_ID(), 'data_jogo', true) ?: date('Y-m-d');
    $hora_limpa = str_replace(array('h', ':'), '', $horario);
    $data_inicio = str_replace('-', '', $data_meta) . 'T' . str_pad($hora_limpa, 4, '0', STR_PAD_LEFT) . '00';
    $google_cal_url = "https://www.google.com/calendar/render?action=TEMPLATE&text=" . urlencode("Assistir $time_casa x $time_fora") . "&dates=$data_inicio/$data_inicio&details=" . urlencode("Jogo ao vivo pela Assistir Jogos: " . get_permalink()) . "&location=" . urlencode($estadio) . "&sf=true&output=xml";
?>

<main class="container mx-auto px-4 py-8">

    <!-- MATCH CENTER HEADER -->
    <section class="bg-card-bg rounded-3xl border border-slate-700 overflow-hidden shadow-2xl mb-8">
        <div class="bg-gradient-to-b from-slate-700/50 to-transparent p-8 text-center">
            <!-- ANÚNCIO TOPO -->
            <div class="mb-6 flex justify-center opacity-50 hover:opacity-100 transition">
                <div class="bg-slate-800 border border-dashed border-slate-600 text-gray-500 text-[10px] py-2 px-10 rounded uppercase">Espaço Publicitário (728x90)</div>
            </div>
            <h1 class="text-white text-3xl md:text-5xl font-black mb-4 uppercase tracking-tighter">
                <?php echo esc_html($time_casa); ?> <span class="text-laranja">vs</span> <?php echo esc_html($time_fora); ?>
            </h1>
            <div class="inline-block bg-laranja/20 text-laranja text-[10px] font-bold px-3 py-1 rounded-full mb-6 tracking-widest uppercase border border-laranja/30">
                <?php echo get_post_meta(get_the_ID(), 'data_jogo', true) ? date_i18n('j \d\e F', strtotime(get_post_meta(get_the_ID(), 'data_jogo', true))) : date_i18n('j \d\e F'); ?> • <?php echo esc_html($horario); ?>
                <?php if ($rodada) echo " • " . esc_html($rodada); ?>
            </div>
            <?php if ($estadio) : ?>
                <div class="text-gray-400 text-xs uppercase tracking-widest flex items-center justify-center gap-2 mb-4">
                    <i class="fas fa-landmark text-laranja"></i> <?php echo esc_html($estadio); ?>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16">
                <!-- Time Casa -->
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 md:w-28 md:h-28 bg-slate-800 rounded-full flex items-center justify-center border-4 border-slate-700 mb-4 shadow-lg overflow-hidden">
                        <?php if ($escudo_casa) : ?>
                            <img src="<?php echo esc_url($escudo_casa); ?>" alt="<?php echo esc_attr($time_casa); ?>" class="w-16 md:w-20 object-contain">
                        <?php else : ?>
                            <i class="fas fa-shield-alt text-4xl text-gray-500"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-white"><?php echo esc_html($time_casa); ?></h2>
                </div>

                <!-- VS -->
                <div class="flex flex-col items-center">
                    <span class="text-4xl md:text-5xl font-black text-slate-600 italic">VS</span>
                </div>

                <!-- Time Fora -->
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 md:w-28 md:h-28 bg-slate-800 rounded-full flex items-center justify-center border-4 border-slate-700 mb-4 shadow-lg overflow-hidden">
                        <?php if ($escudo_fora) : ?>
                            <img src="<?php echo esc_url($escudo_fora); ?>" alt="<?php echo esc_attr($time_fora); ?>" class="w-16 md:w-20 object-contain">
                        <?php else : ?>
                            <i class="fas fa-shield-alt text-4xl text-gray-500"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-white"><?php echo esc_html($time_fora); ?></h2>
                </div>
            </div>
        </div>

        <!-- INFO RÁPIDA -->
        <div class="grid grid-cols-1 md:grid-cols-2 border-t border-slate-700">
            <div class="p-4 text-center border-b md:border-b-0 md:border-r border-slate-700">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Onde Assistir</p>
                <p class="text-laranja font-bold"><?php echo esc_html($onde); ?></p>
            </div>
            <div class="p-4 text-center border-b md:border-b-0 md:border-r border-slate-700">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Status</p>
                <span class="inline-flex items-center gap-1 text-green-400 font-bold">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Confirmado
                </span>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Contador Regressivo -->
            <div id="countdown-container" class="bg-slate-800 rounded-2xl p-6 border border-slate-700 text-center shadow-xl hidden">
                <p class="text-gray-400 text-xs uppercase font-bold tracking-widest mb-3">A bola rola em:</p>
                <div class="flex justify-center gap-4 text-white font-mono text-2xl md:text-4xl">
                    <div class="flex flex-col"><span id="cd-h">00</span><span class="text-[10px] text-gray-500 uppercase">Horas</span></div>
                    <span>:</span>
                    <div class="flex flex-col"><span id="cd-m">00</span><span class="text-[10px] text-gray-500 uppercase">Min</span></div>
                    <span>:</span>
                    <div class="flex flex-col"><span id="cd-s">00</span><span class="text-[10px] text-gray-500 uppercase">Seg</span></div>
                </div>
            </div>

            <!-- Bloco de Análise (AdSense) -->
            <!-- ANÚNCIO MEIO (ACIMA DA ANÁLISE) -->
            <div class="mb-8 flex justify-center opacity-50 hover:opacity-100 transition">
                <div class="bg-slate-800 border border-dashed border-slate-600 text-gray-500 text-[10px] py-4 px-20 rounded uppercase w-full text-center">Publicidade Estratégica (Banner Horizontal)</div>
            </div>

            <?php if ($analise) : ?>
                <section class="bg-slate-800 rounded-2xl p-6 md:p-8 border border-slate-700 shadow-xl">
                    <h3 class="text-xl font-bold text-white mb-6 border-l-4 border-laranja pl-4 uppercase tracking-tight">Análise do Jogo</h3>
                    <div class="prose prose-invert max-w-none text-gray-300 leading-relaxed">
                        <?php echo wpautop($analise); ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- DETALHES DA TRANSMISSÃO -->
            <section class="bg-card-bg rounded-2xl p-6 border border-slate-700">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-tv text-laranja"></i> Como assistir <?php echo esc_html($time_casa); ?> x <?php echo esc_html($time_fora); ?>?
                </h3>
                <div class="prose prose-invert max-w-none text-gray-400">
                    <?php the_content(); ?>
                </div>
                
                <div class="mt-6 flex flex-wrap gap-4">
                    <a href="<?php echo esc_url($link_transmissao); ?>" target="_blank" class="bg-laranja hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-external-link-alt"></i> Acessar Transmissão Oficial
                    </a>
                    <a href="<?php echo esc_url($google_cal_url); ?>" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-6 rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-bell"></i> Me Lembre do Início
                    </a>
                </div>
            </section>

            <!-- ENQUETE DE TORCIDA -->
            <section class="bg-slate-800 rounded-2xl p-6 border border-slate-700">
                <h3 class="text-center font-bold text-white mb-6 uppercase tracking-widest text-sm">Odds de Vitória (Mercado 1x2)</h3>
                <div class="grid grid-cols-3 gap-4">
                    <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-600 hover:border-laranja hover:bg-laranja/10 transition group" aria-label="Odds para vitória do <?php echo esc_attr($time_casa); ?>">
                        <span class="text-xs text-gray-500 uppercase group-hover:text-laranja"><?php echo esc_html($time_casa); ?></span>
                        <span class="text-2xl font-black text-laranja"><?php echo $probabilidade_casa; ?></span>
                    </button>
                    <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-600 hover:border-laranja hover:bg-laranja/10 transition group" aria-label="Odds para empate">
                        <span class="text-xs text-gray-500 uppercase group-hover:text-laranja">Empate</span>
                        <span class="text-2xl font-black text-white"><?php echo $probabilidade_empate; ?></span>
                    </button>
                    <button class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-600 hover:border-laranja hover:bg-laranja/10 transition group" aria-label="Odds para vitória do <?php echo esc_attr($time_fora); ?>">
                        <span class="text-xs text-gray-500 uppercase group-hover:text-laranja"><?php echo esc_html($time_fora); ?></span>
                        <span class="text-2xl font-black text-laranja"><?php echo $probabilidade_fora; ?></span>
                    </button>
                </div>
            </section>
        </div>

        <!-- Coluna Direita (Sidebar) -->
        <aside class="space-y-8">
            <!-- ANÚNCIO SIDEBAR -->
            <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700 flex flex-col items-center gap-2 opacity-50 hover:opacity-100 transition">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Publicidade</span>
                <div class="w-full aspect-square bg-slate-900 border border-dashed border-slate-600 rounded-xl flex items-center justify-center text-gray-600 text-[10px] text-center px-4">
                    Espaço para Banner Quadrado (300x250 ou 336x280)
                </div>
            </div>

            <!-- BARES QUE TRANSMITEM -->
            <section class="bg-card-bg rounded-2xl p-6 border border-slate-700">
                <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-beer text-laranja"></i> Onde ver em Bares
                </h3>
                <div class="space-y-4">
                    <?php
                    $args_bares = array('post_type' => 'bar', 'posts_per_page' => 2);
                    $query_bares = new WP_Query($args_bares);
                    while ($query_bares->have_posts()) : $query_bares->the_post();
                    ?>
                        <div class="p-3 bg-slate-800 rounded-xl border border-slate-700 text-sm">
                            <h4 class="font-bold text-white"><?php the_title(); ?></h4>
                            <p class="text-gray-500 text-xs mt-1"><?php echo get_post_meta(get_the_ID(), 'endereco', true); ?></p>
                            <a href="<?php the_permalink(); ?>" class="text-laranja text-xs mt-2 inline-block">Ver detalhes →</a>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>

            <!-- SHARE -->
            <section class="bg-laranja/10 rounded-2xl p-6 border border-laranja/20 text-center">
                <p class="text-laranja font-bold mb-4">Ajude um amigo a não perder esse jogo!</p>
                <div class="flex justify-center gap-4 text-2xl">
                    <a href="https://api.whatsapp.com/send?text=Onde assistir <?php the_title(); ?>: <?php the_permalink(); ?>" target="_blank" class="text-green-500 hover:scale-110 transition"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="text-blue-400 hover:scale-110 transition"><i class="fab fa-twitter"></i></a>
                    <button onclick="navigator.clipboard.writeText('<?php the_permalink(); ?>')" class="text-gray-400 hover:scale-110 transition"><i class="fas fa-link"></i></button>
                </div>
            </section>
        </aside>
    </div>

    <?php if (function_exists('display_related_posts')) display_related_posts(); ?>
</main>

<?php endwhile; endif; ?>

<?php get_footer(); ?>

<script>
    function updateCountdown() {
        const agora = new Date();
        const horarioJogo = "<?php echo $horario; ?>";
        const [hora, min] = horarioJogo.replace('h', ':').split(':');
        
        const dataJogo = new Date();
        dataJogo.setHours(parseInt(hora), parseInt(min), 0);
        
        const diff = dataJogo - agora;
        
        if (diff > 0) {
            document.getElementById('countdown-container').classList.remove('hidden');
            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('cd-h').innerText = h.toString().padStart(2, '0');
            document.getElementById('cd-m').innerText = m.toString().padStart(2, '0');
            document.getElementById('cd-s').innerText = s.toString().padStart(2, '0');
        } else {
            document.getElementById('countdown-container').innerHTML = '<p class="text-laranja font-bold uppercase animate-pulse"><i class="fas fa-circle text-[8px] mr-1"></i> A partida já começou!</p>';
            document.getElementById('countdown-container').classList.remove('hidden');
        }
    }
    
    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>

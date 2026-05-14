<?php
/**
 * Template Name: Home - Jogos
 */
get_header(); 

// BUSCAR DADOS DOS JOGOS NO INÍCIO PARA O CALENDÁRIO
$jogos_data = array();
$args_jogos = array('post_type' => 'jogo', 'posts_per_page' => -1);
$query_jogos = new WP_Query($args_jogos);
if ($query_jogos->have_posts()) {
    while ($query_jogos->have_posts()) {
        $query_jogos->the_post();
        $jogos_data[] = array(
            'timeCasa' => get_post_meta(get_the_ID(), 'time_casa', true) ?: get_the_title(),
            'timeFora' => get_post_meta(get_the_ID(), 'time_fora', true),
            'escudoCasa' => get_post_meta(get_the_ID(), 'escudo_casa', true),
            'escudoFora' => get_post_meta(get_the_ID(), 'escudo_fora', true),
            'horario'  => get_post_meta(get_the_ID(), 'horario', true),
            'onde'     => implode(', ', wp_get_post_terms(get_the_ID(), 'canal', array('fields' => 'names'))) ?: 'A definir',
            'tipo'     => wp_get_post_terms(get_the_ID(), 'esporte', array('fields' => 'slugs'))[0] ?? 'futebol',
            'campeonato' => wp_get_post_terms(get_the_ID(), 'campeonato', array('fields' => 'names'))[0] ?? '',
            'link'     => get_the_permalink(),
            'data'     => get_post_meta(get_the_ID(), 'data_jogo', true) ?: get_the_date('Y-m-d'),
            'status'   => get_post_meta(get_the_ID(), 'status_jogo', true) ?: 'Agendado',
            'placarCasa' => get_post_meta(get_the_ID(), 'placar_casa', true),
            'placarFora' => get_post_meta(get_the_ID(), 'placar_fora', true),
            'oddCasa'  => get_post_meta(get_the_ID(), 'oddCasa', true) ?: '-',
            'oddEmpate' => get_post_meta(get_the_ID(), 'oddEmpate', true) ?: '-',
            'oddFora'  => get_post_meta(get_the_ID(), 'oddFora', true) ?: '-',
            'rodada'   => get_post_meta(get_the_ID(), 'rodada', true) ?: ''
        );
    }
    
    // ORDENAÇÃO CRONOLÓGICA NO PHP (Garante a ordem antes de chegar no JS)
    usort($jogos_data, function($a, $b) {
        $timeA = str_pad(str_replace('h', ':', $a['horario']), 5, '0', STR_PAD_LEFT);
        $timeB = str_pad(str_replace('h', ':', $b['horario']), 5, '0', STR_PAD_LEFT);
        return strcmp($timeA, $timeB);
    });

    wp_reset_postdata();
}
?>

<main class="container mx-auto px-4 py-8">
    <!-- HERO SECTION -->
    <section class="text-center mb-12">
        <h1 class="text-3xl md:text-5xl font-extrabold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
            Não perca tempo procurando
        </h1>
        <p class="text-gray-300 text-lg md:text-xl mt-4 max-w-2xl mx-auto">
            Descubra em <span class="text-laranja font-bold">5 segundos</span> onde vai passar cada jogo. Legal, gratuito e sempre atualizado.
        </p>
        
        <div id="favorito-setup" class="mt-8 max-w-md mx-auto bg-slate-800/50 p-4 rounded-2xl border border-slate-700">
            <p class="text-xs text-gray-400 mb-3 uppercase font-bold tracking-widest">⭐ Destaque seu time do coração</p>
            <div id="favorito-input-area" class="flex gap-2">
                <input type="text" id="input-time-favorito" placeholder="Ex: Flamengo, Palmeiras..." class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-laranja text-white">
                <button onclick="salvarTimeFavorito()" class="bg-laranja hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Salvar</button>
            </div>
            <div id="time-selecionado-display" class="hidden mt-1 text-sm text-verde-menta font-bold flex items-center justify-between gap-2 bg-verde-menta/10 p-2 rounded-lg border border-verde-menta/20">
                <div class="flex items-center gap-2">
                    <i class="fas fa-heart text-red-500"></i>
                    <span id="nome-time-favorito">Time favorito!</span>
                </div>
                <button onclick="limparTimeFavorito()" class="text-gray-500 hover:text-red-400 text-[10px] uppercase font-black">Alterar</button>
            </div>
        </div>

        <div class="mt-6">
            <a href="#jogos-hoje" id="btn-ir-para-hoje" class="inline-flex items-center gap-2 bg-laranja hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105">
                <i class="fas fa-calendar-day"></i> Jogos de Hoje
            </a>
        </div>
    </section>

    <!-- FILTROS DINÂMICOS -->
    <div class="mb-8 flex flex-wrap justify-center gap-3">
        <button data-filtro="todos" class="btn-filter active bg-laranja text-white px-5 py-2 rounded-full border border-laranja font-medium text-sm transition">Todos</button>
        <?php
        $esportes = get_terms(array('taxonomy' => 'esporte', 'hide_empty' => true));
        foreach ($esportes as $esporte) :
            // Mapeamento simples de ícones (opcional)
            $icon = '⚽'; // Ícone padrão
            if (stripos($esporte->name, 'basquete') !== false) $icon = '🏀';
            if (stripos($esporte->name, 'formula') !== false) $icon = '🏎️';
            if (stripos($esporte->name, 'ufc') !== false || stripos($esporte->name, 'luta') !== false) $icon = '🥊';
            if (stripos($esporte->name, 'vôlei') !== false) $icon = '🏐';
        ?>
            <button data-filtro="<?php echo esc_attr($esporte->slug); ?>" class="btn-filter bg-slate-800 text-gray-200 px-5 py-2 rounded-full border border-slate-600 font-medium text-sm hover:bg-slate-700">
                <?php echo $icon . ' ' . esc_html($esporte->name); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- CALENDÁRIO DE JOGOS (ZEUS STRIP) -->
    <div class="mb-10">
        <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-4 no-scrollbar scroll-smooth" id="calendar-strip">
            <?php
            // Coletar todas as datas que possuem jogos
            $datas_com_jogos = array();
            foreach ($jogos_data as $jogo) {
                if (!empty($jogo['data'])) {
                    $datas_com_jogos[] = $jogo['data'];
                }
            }
            $datas_unicas = array_unique($datas_com_jogos);
            sort($datas_unicas);

            // Se não houver jogos futuros, mostrar ao menos os próximos 3 dias como fallback
            if (empty($datas_unicas)) {
                for($i=0; $i<3; $i++) $datas_unicas[] = date('Y-m-d', strtotime("+$i days"));
            }

            foreach ($datas_unicas as $index => $date) {
                $dia_nome = date_i18n('D', strtotime($date));
                $dia_num = date_i18n('d', strtotime($date));
                $mes_nome = date_i18n('M', strtotime($date));
                $is_today = ($date === date('Y-m-d'));
                $active_class = $is_today ? 'active bg-laranja text-white border-laranja' : 'bg-slate-800 text-gray-400 border-slate-700';
                
                echo "<button data-date='$date' class='calendar-date-btn flex flex-col items-center min-w-[70px] py-3 rounded-2xl border transition-all hover:border-laranja $active_class'>";
                echo "<span class='text-[10px] uppercase font-bold opacity-60'>$dia_nome</span>";
                echo "<span class='text-xl font-black'>$dia_num</span>";
                echo "<span class='text-[9px] uppercase font-bold'>$mes_nome</span>";
                echo "</button>";
            }
            ?>
        </div>
    </div>

    <!-- CARDS DOS JOGOS -->
    <section id="jogos-hoje" aria-label="Jogos do dia">
        <h2 id="jogos-titulo-dinamico" class="text-2xl font-bold border-l-4 border-laranja pl-3 mb-6 text-white uppercase tracking-tight">
            📺 Jogos de Hoje – <?php echo date_i18n('j \d\e F'); ?>
        </h2>
        <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- SKELETON LOADERS (Para evitar CLS) -->
            <?php for($i=0; $i<6; $i++): ?>
            <div class="skeleton-card bg-slate-800/50 rounded-2xl p-5 border border-slate-700 animate-pulse">
                <div class="flex justify-between mb-4">
                    <div class="h-4 w-20 bg-slate-700 rounded"></div>
                    <div class="h-4 w-12 bg-slate-700 rounded"></div>
                </div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 bg-slate-700 rounded-full"></div>
                    <div class="h-6 w-40 bg-slate-700 rounded"></div>
                </div>
                <div class="h-4 w-full bg-slate-700 rounded mb-2"></div>
                <div class="h-10 w-full bg-slate-700 rounded-xl mt-4"></div>
            </div>
            <?php endfor; ?>
        </div>
    </section>

    </section>

    <!-- SEÇÃO ÚLTIMAS NOTÍCIAS -->
    <section class="mt-20">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-white border-l-4 border-laranja pl-4 uppercase tracking-tight">Fique por <span class="text-laranja">Dentro</span></h2>
            <a href="<?php echo home_url('/blog'); ?>" class="text-sm font-bold text-gray-400 hover:text-laranja transition flex items-center gap-2">Ver todas <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $news_query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 3));
            if ($news_query->have_posts()) : while ($news_query->have_posts()) : $news_query->the_post();
            ?>
                <article class="bg-slate-800/50 rounded-2xl overflow-hidden border border-slate-700 hover:border-laranja/30 transition-all group">
                    <div class="aspect-video overflow-hidden">
                        <?php get_asna_thumbnail('medium_large', 'w-full h-full object-cover group-hover:scale-105 transition-transform'); ?>
                    </div>
                    <div class="p-5">
                        <span class="text-[9px] font-black text-laranja uppercase tracking-widest block mb-2"><?php echo get_the_date(); ?></span>
                        <h3 class="text-lg font-bold text-white mb-3 line-clamp-2 group-hover:text-laranja transition-colors">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <a href="<?php the_permalink(); ?>" class="text-xs font-bold text-gray-400 group-hover:text-white flex items-center gap-1 transition-colors">
                            Ler mais <i class="fas fa-chevron-right text-[8px] text-laranja"></i>
                        </a>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); else : ?>
                <p class="text-gray-500 italic col-span-full">Nenhuma notícia para exibir ainda.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
window.jogosData = <?php echo json_encode($jogos_data); ?>;
window.todayDate = '<?php echo date('Y-m-d'); ?>';

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('cards-container');
    const tituloDinamico = document.getElementById('jogos-titulo-dinamico');
    const dateBtns = document.querySelectorAll('.calendar-date-btn');
    const filterBtns = document.querySelectorAll('.btn-filter');
    const btnIrParaHoje = document.getElementById('btn-ir-para-hoje');
    
    let currentFilter = 'todos';
    let currentDate = window.todayDate;

    function renderJogos() {
        const favoriteTeam = localStorage.getItem('meuTimeFavorito') || '';
        const agora = new Date();
        
        // Sincronizar UI do Favorito
        const inputArea = document.getElementById('favorito-input-area');
        const displayArea = document.getElementById('time-selecionado-display');
        const nomeTimeText = document.getElementById('nome-time-favorito');

        if (favoriteTeam && favoriteTeam.trim() !== "") {
            inputArea.classList.add('hidden');
            displayArea.classList.remove('hidden');
            nomeTimeText.innerText = favoriteTeam.toUpperCase();
        } else {
            inputArea.classList.remove('hidden');
            displayArea.classList.add('hidden');
        }

        // Atualizar Título Dinâmico
        const hojeStr = new Date().toISOString().split('T')[0];
        const selecionada = new Date(currentDate + 'T12:00:00'); 
        const opcoesData = { day: 'numeric', month: 'long' };
        const dataFormatada = selecionada.toLocaleDateString('pt-BR', opcoesData);
        
        if (currentDate === hojeStr) {
            tituloDinamico.innerHTML = `📺 Jogos de Hoje – ${dataFormatada}`;
        } else {
            const diaSemana = selecionada.toLocaleDateString('pt-BR', { weekday: 'long' });
            tituloDinamico.innerHTML = `📺 Jogos de ${diaSemana}, ${dataFormatada}`;
        }

        container.innerHTML = '';
        
        // Função auxiliar para converter horário em minutos (ex: 16:00 -> 960)
        const getMinutes = (h) => {
            if (!h || typeof h !== 'string') return 0;
            const match = h.match(/(\d{1,2})[:h](\d{2})/);
            return match ? (parseInt(match[1]) * 60 + parseInt(match[2])) : 0;
        };

        // 1. Filtrar por data e categoria
        let filtrados = window.jogosData.filter(jogo => {
            const matchFiltro = currentFilter === 'todos' || jogo.tipo === currentFilter;
            const matchDate = jogo.data === currentDate;
            return matchFiltro && matchDate;
        });

        // 2. Aplicar Ordenação Híbrida (Favorito primeiro, depois Horário ASC)
        // Forçando a lógica: (a - b) para garantir que 16:00 < 20:30
        filtrados.sort((a, b) => {
            const aIsFav = favoriteTeam && (a.timeCasa.toLowerCase().includes(favoriteTeam.toLowerCase()) || a.timeFora.toLowerCase().includes(favoriteTeam.toLowerCase()));
            const bIsFav = favoriteTeam && (b.timeCasa.toLowerCase().includes(favoriteTeam.toLowerCase()) || b.timeFora.toLowerCase().includes(favoriteTeam.toLowerCase()));

            if (aIsFav && !bIsFav) return -1;
            if (!aIsFav && bIsFav) return 1;

            const minA = getMinutes(a.horario);
            const minB = getMinutes(b.horario);
            
            return minA - minB;
        });

        if (filtrados.length === 0) {
            container.innerHTML = '<div class="col-span-full py-20 text-center text-gray-500 italic">Nenhum jogo programado para este dia.</div>';
            return;
        }

        filtrados.forEach(jogo => {
            const isFav = favoriteTeam && (jogo.timeCasa.toLowerCase().includes(favoriteTeam.toLowerCase()) || jogo.timeFora.toLowerCase().includes(favoriteTeam.toLowerCase()));
            const borderClass = isFav ? 'border-laranja shadow-[0_0_20px_rgba(249,115,22,0.2)]' : 'border-slate-700/50';
            
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

            const card = `
                <div class="bg-slate-800/40 rounded-3xl p-6 border ${borderClass} hover:border-laranja/30 transition-all group relative flex flex-col h-full">
                    
                    ${statusBadge}

                    ${isFav ? `
                        <div class="absolute -top-2 -right-2 bg-laranja text-white text-[8px] font-black px-2 py-1 rounded-lg shadow-lg z-10 flex items-center gap-1 animate-bounce">
                            <i class="fas fa-star"></i> SEU TIME
                        </div>
                    ` : ''}

                    <!-- CABEÇALHO DO CARD -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-slate-700/50 text-gray-400 text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-wider">${jogo.tipo}</span>
                            <span class="bg-laranja/10 text-laranja text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-wider flex items-center gap-1">
                                <i class="fas fa-trophy text-[8px]"></i> ${jogo.campeonato} ${jogo.rodada ? ' • ' + jogo.rodada : ''}
                            </span>
                        </div>
                    </div>

                    <!-- TIMES E PLACAR -->
                    <div class="flex items-center justify-between gap-3 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <img src="${jogo.escudoCasa}" class="w-8 h-8 rounded-full border-2 border-slate-800 bg-white p-1 object-contain" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/logtipo_2.webp'">
                                <img src="${jogo.escudoFora}" class="w-8 h-8 rounded-full border-2 border-slate-800 bg-white p-1 object-contain" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/logtipo_2.webp'">
                            </div>
                            <h3 class="font-bold text-white text-base truncate max-w-[150px]">${jogo.timeCasa} <span class="text-gray-500 font-normal mx-1">x</span> ${jogo.timeFora}</h3>
                        </div>

                        ${(jogo.status === 'Ao Vivo' || jogo.status === 'Encerrado') ? `
                            <div class="flex items-center gap-2 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-laranja/30 shadow-lg shadow-laranja/5">
                                <span class="text-xl font-black text-white tabular-nums leading-none">${jogo.placarCasa || '0'}</span>
                                <span class="text-laranja font-black leading-none">-</span>
                                <span class="text-xl font-black text-white tabular-nums leading-none">${jogo.placarFora || '0'}</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- INFO EXTRA -->
                    <div class="flex items-center gap-4 mb-6 text-gray-400 text-[11px] font-bold">
                        <span class="flex items-center gap-1.5"><i class="far fa-clock text-laranja"></i> ${jogo.horario}</span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-tv text-laranja text-[10px]"></i> ${jogo.onde || 'A definir'}</span>
                    </div>

                    <!-- ODDS SECTION -->
                    <div class="mt-auto">
                        <p class="text-center text-[9px] font-black text-gray-500 uppercase tracking-widest mb-3">Odds de Vitória</p>
                        <div class="grid grid-cols-3 gap-2 mb-6">
                            <div class="bg-slate-900/50 rounded-xl p-2.5 text-center border border-slate-700/50">
                                <span class="block text-[7px] text-gray-500 uppercase font-black mb-1">Casa</span>
                                <span class="text-xs font-black text-laranja">${jogo.oddCasa}</span>
                            </div>
                            <div class="bg-slate-900/50 rounded-xl p-2.5 text-center border border-slate-700/50">
                                <span class="block text-[7px] text-gray-500 uppercase font-black mb-1">Empate</span>
                                <span class="text-xs font-black text-white">${jogo.oddEmpate}</span>
                            </div>
                            <div class="bg-slate-900/50 rounded-xl p-2.5 text-center border border-slate-700/50">
                                <span class="block text-[7px] text-gray-500 uppercase font-black mb-1">Fora</span>
                                <span class="text-xs font-black text-laranja">${jogo.oddFora}</span>
                            </div>
                        </div>

                        <!-- FOOTER BUTTONS -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-700/50">
                            <button class="text-gray-500 hover:text-white text-[10px] font-bold flex items-center gap-1.5 transition-colors">
                                <i class="fab fa-whatsapp"></i> Compartilhar
                            </button>
                            <a href="${jogo.link}" class="bg-laranja/10 hover:bg-laranja text-laranja hover:text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    // Funções do Time Favorito
    window.salvarTimeFavorito = function() {
        const input = document.getElementById('input-time-favorito');
        const time = input.value.trim();
        if (time) {
            localStorage.setItem('meuTimeFavorito', time);
            renderJogos();
        }
    };

    window.limparTimeFavorito = function() {
        localStorage.removeItem('meuTimeFavorito');
        renderJogos();
    };

    dateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            dateBtns.forEach(b => b.classList.remove('active', 'bg-laranja', 'text-white', 'border-laranja'));
            dateBtns.forEach(b => b.classList.add('bg-slate-800', 'text-gray-400', 'border-slate-700'));
            this.classList.add('active', 'bg-laranja', 'text-white', 'border-laranja');
            this.classList.remove('bg-slate-800', 'text-gray-400', 'border-slate-700');
            currentDate = this.dataset.date;
            renderJogos();
        });
    });

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active', 'bg-laranja', 'text-white', 'border-laranja'));
            filterBtns.forEach(b => b.classList.add('bg-slate-800', 'text-gray-200', 'border-slate-600'));
            this.classList.add('active', 'bg-laranja', 'text-white', 'border-laranja');
            this.classList.remove('bg-slate-800', 'text-gray-200', 'border-slate-600');
            currentFilter = this.dataset.filtro;
            renderJogos();
        });
    });

    // Lógica do botão "Jogos de Hoje" do topo
    if (btnIrParaHoje) {
        btnIrParaHoje.addEventListener('click', function(e) {
            e.preventDefault();
            const btnHoje = document.querySelector(`.calendar-date-btn[data-date="${window.todayDate}"]`);
            if (btnHoje) {
                btnHoje.click(); // Reseta a data e renderiza
            }
            document.getElementById('jogos-hoje').scrollIntoView({ behavior: 'smooth' });
        });
    }

    renderJogos();
});
</script>
<?php get_footer(); ?>

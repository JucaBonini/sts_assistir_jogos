<?php
/**
 * Template Name: Copa Hub
 */
get_header(); 

// BUSCAR JOGOS DA COPA (No novo CPT jogo_copa)
$args_copa = array(
    'post_type' => 'jogo_copa',
    'posts_per_page' => -1,
);
$query_copa = new WP_Query($args_copa);
$jogos_copa = array();

if ($query_copa->have_posts()) {
    while ($query_copa->have_posts()) {
        $query_copa->the_post();
        $jogos_copa[] = array(
            'timeCasa' => get_post_meta(get_the_ID(), 'time_casa', true) ?: get_the_title(),
            'timeFora' => get_post_meta(get_the_ID(), 'time_fora', true),
            'escudoCasa' => get_post_meta(get_the_ID(), 'escudo_casa', true),
            'escudoFora' => get_post_meta(get_the_ID(), 'escudo_fora', true),
            'horario'  => get_post_meta(get_the_ID(), 'horario', true),
            'data'     => get_post_meta(get_the_ID(), 'data_jogo', true) ?: get_the_date('Y-m-d'),
            'cidade'   => get_post_meta(get_the_ID(), 'cidade_sede', true),
            'pais'     => get_post_meta(get_the_ID(), 'pais_sede', true),
            'timeCasaId' => get_post_meta(get_the_ID(), 'time_casa_id', true),
            'timeForaId' => get_post_meta(get_the_ID(), 'time_fora_id', true),
            'grupo'    => get_post_meta(get_the_ID(), 'grupo_copa', true),
            'fase'     => get_post_meta(get_the_ID(), 'fase_copa', true),
            'placarCasa' => get_post_meta(get_the_ID(), 'placar_casa', true),
            'placarFora' => get_post_meta(get_the_ID(), 'placar_fora', true),
            'status'   => get_post_meta(get_the_ID(), 'status_jogo', true) ?: 'Agendado',
            'onde'     => implode(', ', wp_get_post_terms(get_the_ID(), 'canal', array('fields' => 'names'))) ?: 'A definir',
            'link'     => get_the_permalink()
        );
    }
    wp_reset_postdata();
}

// Lógica para processar classificação
$grupos_data = array();
if (!empty($jogos_copa)) {
    foreach ($jogos_copa as $jogo) {
        if (!$jogo['grupo']) continue;
        $grp = $jogo['grupo'];
        
        if (!isset($grupos_data[$grp])) $grupos_data[$grp] = array();
        
        // Inicializar times se não existirem
        $casa_id = $jogo['timeCasaId'];
        $fora_id = $jogo['timeForaId'];
        $nome_casa = $casa_id ? get_the_title($casa_id) : $jogo['timeCasa'];
        $nome_fora = $fora_id ? get_the_title($fora_id) : $jogo['timeFora'];
        $link_casa = $casa_id ? get_permalink($casa_id) : '#';
        $link_fora = $fora_id ? get_permalink($fora_id) : '#';

        foreach (array('Casa' => array('nome' => $nome_casa, 'link' => $link_casa, 'id' => $casa_id), 
                       'Fora' => array('nome' => $nome_fora, 'link' => $link_fora, 'id' => $fora_id)) as $tipo => $data) {
            $nome = $data['nome'];
            if (!isset($grupos_data[$grp][$nome])) {
                $escudo_final = $data['id'] ? get_the_post_thumbnail_url($data['id'], 'thumbnail') : ($tipo == 'Casa' ? $jogo['escudoCasa'] : $jogo['escudoFora']);
                $grupos_data[$grp][$nome] = array(
                    'P' => 0, 'J' => 0, 'V' => 0, 'E' => 0, 'D' => 0, 'GP' => 0, 'GC' => 0, 'SG' => 0,
                    'escudo' => $escudo_final,
                    'link' => $data['link']
                );
            }
        }

        // Se o jogo encerrou, computar pontos
        if ($jogo['status'] === 'Encerrado' && $jogo['placarCasa'] !== '' && $jogo['placarFora'] !== '') {
            $gC = (int)$jogo['placarCasa'];
            $gF = (int)$jogo['placarFora'];
            $tC = $nome_casa;
            $tF = $nome_fora;

            $grupos_data[$grp][$tC]['J']++;
            $grupos_data[$grp][$tF]['J']++;
            $grupos_data[$grp][$tC]['GP'] += $gC;
            $grupos_data[$grp][$tC]['GC'] += $gF;
            $grupos_data[$grp][$tF]['GP'] += $gF;
            $grupos_data[$grp][$tF]['GC'] += $gC;

            if ($gC > $gF) {
                $grupos_data[$grp][$tC]['P'] += 3;
                $grupos_data[$grp][$tC]['V']++;
                $grupos_data[$grp][$tF]['D']++;
            } elseif ($gC < $gF) {
                $grupos_data[$grp][$tF]['P'] += 3;
                $grupos_data[$grp][$tF]['V']++;
                $grupos_data[$grp][$tC]['D']++;
            } else {
                $grupos_data[$grp][$tC]['P'] += 1;
                $grupos_data[$grp][$tF]['P'] += 1;
                $grupos_data[$grp][$tC]['E']++;
                $grupos_data[$grp][$tF]['E']++;
            }
            $grupos_data[$grp][$tC]['SG'] = $grupos_data[$grp][$tC]['GP'] - $grupos_data[$grp][$tC]['GC'];
            $grupos_data[$grp][$tF]['SG'] = $grupos_data[$grp][$tF]['GP'] - $grupos_data[$grp][$tF]['GC'];
        }
    }

    // Ordenar cada grupo por pontos, vitórias e saldo
    foreach ($grupos_data as $grp => &$times) {
        uasort($times, function($a, $b) {
            if ($a['P'] != $b['P']) return $b['P'] - $a['P'];
            if ($a['V'] != $b['V']) return $b['V'] - $a['V'];
            return $b['SG'] - $a['SG'];
        });
    }
    ksort($grupos_data); // Ordenar grupos A, B, C...
}

// Ordenar jogos por data/horário para a seção de Próximas Batalhas
if (!empty($jogos_copa)) {
    usort($jogos_copa, function($a, $b) {
        $dateA = $a['data'] . ' ' . str_replace('h', ':', $a['horario']);
        $dateB = $b['data'] . ' ' . str_replace('h', ':', $b['horario']);
        return strtotime($dateA) - strtotime($dateB);
    });
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
    
    .copa-font { font-family: 'Outfit', sans-serif; }
    .glass-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(249, 115, 22, 0.1);
    }
    .glass-card:hover {
        border-color: rgba(249, 115, 22, 0.4);
        box-shadow: 0 0 30px rgba(249, 115, 22, 0.1);
    }
    .text-gradient {
        background: linear-gradient(to right, #fff, #f97316);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<div class="bg-slate-950 text-white min-h-screen copa-font">
    <!-- HERO COPA -->
    <section class="relative py-20 overflow-hidden bg-gradient-to-b from-laranja/20 to-transparent">
        <div class="container mx-auto px-4 text-center relative z-10">
            <span class="inline-block bg-laranja text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-4">Exclusivo Copa 2026</span>
            <h1 class="text-4xl md:text-7xl font-black mb-6 tracking-tighter italic uppercase text-gradient">
                Onde Assistir a Copa do Mundo
            </h1>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto font-medium">
                Siga todas as seleções rumo ao topo do mundo. Jogos, horários e transmissões oficiais em tempo real.
            </p>
        </div>
        
        <!-- ELEMENTO DECORATIVO (Bandeiras das Sedes) -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 flex gap-4 opacity-10 pointer-events-none">
            <span class="text-8xl">🇺🇸</span>
            <span class="text-8xl">🇲🇽</span>
            <span class="text-8xl">🇨🇦</span>
        </div>
    </section>

    <main class="container mx-auto px-4 pb-20">
        <!-- PRÓXIMA BATALHA (DESTAQUE TOPO) -->
        <?php
        $proximo_jogo = get_posts(array(
            'post_type' => 'jogo_copa',
            'posts_per_page' => 1,
            'meta_key' => 'data_jogo',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'data_jogo',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        ));

        if ($proximo_jogo) : 
            $jogo = $proximo_jogo[0];
            $meta = get_post_custom($jogo->ID);
            $casa_id = $meta['time_casa_id'][0] ?? '';
            $fora_id = $meta['time_fora_id'][0] ?? '';
        ?>
        <section class="mb-16 mt-10 relative overflow-hidden rounded-3xl bg-black border-4 border-laranja shadow-[0_0_50px_rgba(230,126,34,0.3)]">
            <div class="absolute inset-0 bg-gradient-to-br from-laranja/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 p-8 md:p-12 flex flex-col items-center text-center">
                
                <div class="bg-laranja text-black px-6 py-1 rounded-full text-xs font-black uppercase tracking-widest mb-8 animate-pulse">
                    🔥 PRÓXIMA BATALHA
                </div>

                <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-20 w-full mb-10">
                    <!-- Time Casa -->
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-32 h-32 md:w-48 md:h-48 bg-white rounded-full p-3 shadow-2xl border-4 border-laranja/20 transform hover:scale-110 transition-transform duration-500">
                            <img src="<?php echo get_the_post_thumbnail_url($casa_id, 'full'); ?>" alt="" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-2xl md:text-5xl font-black uppercase text-white tracking-tighter"><?php echo get_the_title($casa_id); ?></h3>
                    </div>

                    <!-- VS -->
                    <div class="flex flex-col items-center">
                        <span class="text-6xl md:text-9xl font-black text-white/5 italic">VS</span>
                        <div class="absolute flex flex-col items-center justify-center">
                            <p class="text-3xl md:text-5xl font-black text-laranja uppercase italic"><?php echo $meta['horario'][0] ?? ''; ?></p>
                            <p class="text-sm md:text-lg text-gray-400 font-bold mt-2"><?php echo date('d/m/Y', strtotime($meta['data_jogo'][0])); ?></p>
                        </div>
                    </div>

                    <!-- Time Fora -->
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-32 h-32 md:w-48 md:h-48 bg-white rounded-full p-3 shadow-2xl border-4 border-laranja/20 transform hover:scale-110 transition-transform duration-500">
                            <img src="<?php echo get_the_post_thumbnail_url($fora_id, 'full'); ?>" alt="" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-2xl md:text-5xl font-black uppercase text-white tracking-tighter"><?php echo get_the_title($fora_id); ?></h3>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-4">
                    <a href="<?php echo get_permalink($jogo->ID); ?>" class="bg-white text-black px-12 py-5 rounded-full font-black uppercase tracking-tighter hover:bg-laranja hover:text-white transition-all shadow-xl flex items-center justify-center gap-3 text-lg">
                        Assistir Agora <i class="fas fa-play"></i>
                    </a>
                    <div class="bg-slate-900 border-2 border-white/10 px-8 py-5 rounded-full flex items-center gap-3 text-gray-300">
                        <i class="fas fa-map-marker-alt text-laranja"></i>
                        <span class="text-sm font-bold uppercase"><?php echo $meta['estadio'][0] ?? 'Estádio Principal'; ?></span>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <!-- CLASSIFICAÇÃO DOS GRUPOS -->
        <section class="mb-20">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-black uppercase italic tracking-tight border-l-4 border-laranja pl-4">Tabelas de <span class="text-laranja">Classificação</span></h2>
            </div>

            <?php if (empty($grupos_data)) : ?>
                <div class="bg-slate-900/30 border border-slate-800 rounded-3xl p-8 text-center text-gray-500 italic">
                    Cadastre jogos com o campo "Grupo" preenchido para gerar as tabelas automaticamente.
                </div>
            <?php else : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($grupos_data as $nome_grupo => $times) : ?>
                        <div class="glass-card rounded-3xl overflow-hidden transition-all duration-300">
                            <div class="bg-laranja/10 text-laranja px-6 py-4 font-black uppercase italic tracking-widest text-xs border-b border-laranja/5">
                                Classificação - <?php echo $nome_grupo; ?>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-[10px] copa-font">
                                    <thead>
                                        <tr class="text-gray-500 uppercase border-b border-slate-800/50 bg-slate-950/30">
                                            <th class="px-4 py-4 font-bold">Seleção</th>
                                            <th class="px-2 py-4 text-center font-black text-white bg-laranja/5">Pts</th>
                                            <th class="px-2 py-4 text-center">J</th>
                                            <th class="px-1 py-4 text-center">V</th>
                                            <th class="px-1 py-4 text-center">E</th>
                                            <th class="px-1 py-4 text-center">D</th>
                                            <th class="px-1 py-4 text-center hidden md:table-cell">GP</th>
                                            <th class="px-1 py-4 text-center hidden md:table-cell">GC</th>
                                            <th class="px-2 py-4 text-center">SG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $pos = 1;
                                        foreach ($times as $nome_time => $stats) : 
                                            $bg_pos = ($pos <= 2) ? 'bg-laranja/5' : '';
                                        ?>
                                            <tr class="border-b border-slate-800/30 hover:bg-slate-800/40 transition-colors <?php echo $bg_pos; ?>">
                                                <td class="px-4 py-4 flex items-center gap-3">
                                                    <span class="w-4 text-[9px] <?php echo ($pos <= 2) ? 'text-laranja' : 'text-gray-600'; ?> font-black"><?php echo $pos++; ?>°</span>
                                                    <img src="<?php echo $stats['escudo']; ?>" class="w-6 h-6 object-contain rounded-sm shadow-sm">
                                                    <a href="<?php echo $stats['link']; ?>" class="font-black text-white uppercase tracking-tighter hover:text-laranja transition-colors"><?php echo $nome_time; ?></a>
                                                </td>
                                                <td class="px-2 py-4 text-center font-black text-white text-xs bg-laranja/5"><?php echo $stats['P']; ?></td>
                                                <td class="px-2 py-4 text-center text-gray-400 font-bold"><?php echo $stats['J']; ?></td>
                                                <td class="px-1 py-4 text-center text-gray-500"><?php echo $stats['V']; ?></td>
                                                <td class="px-1 py-4 text-center text-gray-500"><?php echo $stats['E']; ?></td>
                                                <td class="px-1 py-4 text-center text-gray-500"><?php echo $stats['D']; ?></td>
                                                <td class="px-1 py-4 text-center text-gray-500 hidden md:table-cell"><?php echo $stats['GP']; ?></td>
                                                <td class="px-1 py-4 text-center text-gray-500 hidden md:table-cell"><?php echo $stats['GC']; ?></td>
                                                <td class="px-2 py-4 text-center font-bold <?php echo ($stats['SG'] > 0) ? 'text-green-500' : ($stats['SG'] < 0 ? 'text-red-500' : 'text-gray-500'); ?>">
                                                    <?php echo ($stats['SG'] > 0 ? '+' : '') . $stats['SG']; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- MATA-MATA (BRACKET) - COMPACTO ALTO CONTRASTE -->
    <section class="mb-16" aria-labelledby="bracket-title">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-2 h-8 bg-laranja rounded-full shadow-[0_0_15px_#E67E22]"></div>
            <h2 id="bracket-title" class="text-2xl font-black uppercase italic tracking-tighter text-white">Chave do Mata-Mata</h2>
        </div>

        <div class="overflow-x-auto pb-8 -mx-4 px-4 no-scrollbar outline-none focus:ring-2 focus:ring-white rounded-xl" tabindex="0" role="region">
            <div class="min-w-[1000px] flex gap-6 relative py-6">
                
                <?php 
                $fases_mata = array(
                    'Rodada de 32' => 'R32',
                    'Oitavas de Final' => 'R16',
                    'Quartas de Final' => 'QF',
                    'Semifinal' => 'SF',
                    'Final' => 'F'
                );

                foreach ($fases_mata as $label => $slug) : 
                    $jogos_fase = get_posts(array(
                        'post_type' => 'jogo_copa',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array('key' => 'fase_copa', 'value' => $label)
                        ),
                        'orderby' => 'meta_value',
                        'meta_key' => 'match_id',
                        'order' => 'ASC'
                    ));
                ?>
                    <div class="flex-1 flex flex-col gap-10 justify-around" role="list" aria-label="<?php echo $label; ?>">
                        <div class="text-center mb-4">
                            <h3 class="bg-yellow-400 text-black px-3 py-1 rounded text-[10px] font-black uppercase tracking-tighter shadow-[3px_3px_0px_#000]">
                                <?php echo $label; ?>
                            </h3>
                        </div>

                        <?php if ($jogos_fase) : foreach ($jogos_fase as $jogo_obj) : 
                            $meta = get_post_custom($jogo_obj->ID);
                            $casa_id = $meta['time_casa_id'][0] ?? '';
                            $fora_id = $meta['time_fora_id'][0] ?? '';
                            $gols_casa = $meta['placar_casa'][0] ?? '0';
                            $gols_fora = $meta['placar_fora'][0] ?? '0';
                            $match_id = $meta['match_id'][0] ?? 'J??';
                        ?>
                            <div role="listitem">
                                <a href="<?php echo get_permalink($jogo_obj->ID); ?>" 
                                   class="block bg-black p-2 rounded border-2 border-white hover:border-yellow-400 transition-all w-36">
                                    
                                    <div class="flex justify-between items-center mb-2 border-b border-gray-800 pb-1">
                                        <span class="text-[8px] font-black text-yellow-400"><?php echo $match_id; ?></span>
                                        <span class="text-[7px] font-bold text-white"><?php echo $meta['data_jogo'][0] ?? ''; ?></span>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-6 h-4 bg-white rounded-sm overflow-hidden flex-shrink-0">
                                                    <?php if ($casa_id) : ?>
                                                        <img src="<?php echo get_the_post_thumbnail_url($casa_id, 'thumbnail'); ?>" alt="" class="w-full h-full object-cover">
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-[9px] font-black text-white uppercase truncate w-16"><?php echo get_the_title($casa_id) ?: 'TBD'; ?></span>
                                            </div>
                                            <span class="text-xs font-black text-yellow-400"><?php echo $gols_casa; ?></span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-6 h-4 bg-white rounded-sm overflow-hidden flex-shrink-0">
                                                    <?php if ($fora_id) : ?>
                                                        <img src="<?php echo get_the_post_thumbnail_url($fora_id, 'thumbnail'); ?>" alt="" class="w-full h-full object-cover">
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-[9px] font-black text-white uppercase truncate w-16"><?php echo get_the_title($fora_id) ?: 'TBD'; ?></span>
                                            </div>
                                            <span class="text-xs font-black text-yellow-400"><?php echo $gols_fora; ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; else: ?>
                            <?php for($i=0; $i < ($slug == 'R32' ? 16 : ($slug == 'R16' ? 8 : ($slug == 'QF' ? 4 : ($slug == 'SF' ? 2 : 1)))); $i++) : ?>
                                <div class="w-36 h-16 border border-white/20 bg-slate-950 rounded flex items-center justify-center" aria-hidden="true">
                                    <span class="text-[8px] font-black text-white/30 uppercase italic">AGUARDANDO...</span>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>
        <section class="mb-20">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-black uppercase italic tracking-tight border-l-4 border-laranja pl-4">Seleções <span class="text-laranja">Confirmadas</span></h2>
            </div>

            <?php
            $todas_selecoes = get_posts(array('post_type' => 'selecao', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
            if ($todas_selecoes) : ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($todas_selecoes as $sel) : 
                        $flag_url = get_the_post_thumbnail_url($sel->ID, 'medium') ?: get_template_directory_uri().'/assets/images/imagens-post.webp';
                        $cor_sel = get_post_meta($sel->ID, 'cor_primaria', true) ?: '#1e293b';
                        $rank_sel = get_post_meta($sel->ID, 'ranking_fifa', true) ?: '--';
                        $part_sel = get_post_meta($sel->ID, 'participacoes', true) ?: '1ª participação';
                    ?>
                        <a href="<?php echo get_permalink($sel->ID); ?>" class="glass-card rounded-3xl overflow-hidden group hover:-translate-y-2 transition-all duration-500 shadow-xl border border-white/5 flex flex-col">
                            <!-- Header Colorido -->
                            <div class="h-16 relative w-full" style="background-color: <?php echo $cor_sel; ?>;">
                                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-transparent"></div>
                                <!-- Bandeira Flutuante -->
                                <div class="absolute -bottom-6 left-6">
                                    <img src="<?php echo $flag_url; ?>" class="w-14 h-14 object-cover rounded-2xl border-4 border-slate-900 shadow-2xl group-hover:scale-110 transition-transform duration-500">
                                </div>
                            </div>
                            
                            <!-- Conteúdo do Card -->
                            <div class="pt-10 px-6 pb-6 flex-1">
                                <h3 class="text-xl font-black uppercase italic tracking-tighter text-white mb-4 group-hover:text-laranja transition"><?php echo $sel->post_title; ?></h3>
                                
                                <div class="flex items-center justify-between border-t border-white/5 pt-4">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest">Ranking FIFA</span>
                                        <span class="font-black text-white text-sm">#<?php echo $rank_sel; ?></span>
                                    </div>
                                    <div class="flex flex-col text-right">
                                        <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest">Histórico</span>
                                        <span class="font-bold text-gray-400 text-[10px]"><?php echo $part_sel; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botão Detalhes -->
                            <div class="px-6 pb-6 mt-auto">
                                <div class="w-full py-2 bg-white/5 group-hover:bg-laranja/20 rounded-xl text-center text-[9px] font-black uppercase tracking-widest text-gray-400 group-hover:text-laranja transition-all">
                                    Ver Elenco Completo
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="bg-slate-900/30 border border-slate-800 rounded-2xl p-6 text-center text-xs text-gray-600 italic">
                    Cadastre as seleções participantes no menu "Seleções" para que elas apareçam aqui.
                </div>
            <?php endif; ?>
        </section>

        <!-- PRÓXIMOS JOGOS DA COPA -->
        <section>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-black uppercase italic tracking-tight border-l-4 border-laranja pl-4">Próximas <span class="text-laranja">Batalhas</span></h2>
                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sedes: EUA | MEX | CAN</div>
            </div>

            <?php if (empty($jogos_copa)) : ?>
                <div class="bg-slate-900/50 rounded-3xl p-12 text-center border border-slate-800">
                    <i class="fas fa-futbol text-4xl text-slate-700 mb-4"></i>
                    <p class="text-gray-500 italic">Nenhum jogo da Copa cadastrado ainda.<br><span class="text-xs">Cadastre um jogo e use o evento "Copa do Mundo 2026" para exibir aqui.</span></p>
                </div>
            <?php else : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($jogos_copa as $jogo) : 
                        $casa_id = $jogo['timeCasaId'];
                        $fora_id = $jogo['timeForaId'];
                        $nome_casa = $casa_id ? get_the_title($casa_id) : $jogo['timeCasa'];
                        $nome_fora = $fora_id ? get_the_title($fora_id) : $jogo['timeFora'];
                        $link_casa = $casa_id ? get_permalink($casa_id) : '#';
                        $link_fora = $fora_id ? get_permalink($fora_id) : '#';
                        $escudo_casa = $casa_id ? get_the_post_thumbnail_url($casa_id, 'thumbnail') : $jogo['escudoCasa'];
                        $escudo_fora = $fora_id ? get_the_post_thumbnail_url($fora_id, 'thumbnail') : $jogo['escudoFora'];

                        $flag = '🇺🇸';
                        if ($jogo['pais'] === 'México') $flag = '🇲🇽';
                        if ($jogo['pais'] === 'Canadá') $flag = '🇨🇦';
                    ?>
                        <div class="glass-card rounded-3xl p-6 group transition-all duration-500">
                            <!-- INFO HEADER -->
                            <div class="flex justify-between items-center mb-6">
                                <span class="bg-laranja/20 text-laranja text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-wider border border-laranja/10">
                                    <?php echo $jogo['fase']; ?> <?php echo $jogo['grupo'] ? ' • ' . $jogo['grupo'] : ''; ?>
                                </span>
                                <span class="text-[14px]" title="<?php echo $jogo['pais']; ?>"><?php echo $flag; ?></span>
                            </div>

                            <!-- MATCHUP -->
                            <div class="flex flex-col items-center gap-4 mb-6">
                                <div class="flex items-center justify-center gap-6 w-full">
                                    <div class="flex flex-col items-center gap-2 flex-1">
                                        <a href="<?php echo $link_casa; ?>">
                                            <img src="<?php echo $escudo_casa; ?>" class="w-14 h-14 object-contain bg-white rounded-full p-1 shadow-lg group-hover:scale-110 transition-transform">
                                        </a>
                                        <a href="<?php echo $link_casa; ?>" class="text-xs font-black text-center uppercase tracking-tighter hover:text-laranja transition-colors"><?php echo $nome_casa; ?></a>
                                    </div>
                                    
                                            <div class="flex flex-col items-center">
                                                <?php if ($jogo['status'] === 'Agendado') : ?>
                                                    <?php 
                                                    // Regra Automática do AO VIVO (5 min antes até 120 min depois)
                                                    $timestamp_jogo = strtotime($jogo['data'] . ' ' . str_replace('h', ':', $jogo['horario']));
                                                    $agora = time();
                                                    $diff_minutos = ($agora - $timestamp_jogo) / 60;
                                                    
                                                    if ($diff_minutos >= -5 && $diff_minutos <= 120) : ?>
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="text-3xl font-black tabular-nums"><?php echo $jogo['placarCasa']; ?></span>
                                                            <span class="text-laranja font-black">-</span>
                                                            <span class="text-3xl font-black tabular-nums"><?php echo $jogo['placarFora']; ?></span>
                                                        </div>
                                                        <span class="flex items-center gap-1 text-[8px] font-bold text-red-500 animate-pulse">
                                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> AO VIVO
                                                        </span>
                                                    <?php else : ?>
                                                        <div class="text-laranja font-black italic text-xl">VS</div>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-3xl font-black tabular-nums"><?php echo $jogo['placarCasa']; ?></span>
                                                        <span class="text-laranja font-black">-</span>
                                                        <span class="text-3xl font-black tabular-nums"><?php echo $jogo['placarFora']; ?></span>
                                                    </div>
                                                    <?php if ($jogo['status'] === 'Ao Vivo') : ?>
                                                        <span class="flex items-center gap-1 text-[8px] font-bold text-red-500 animate-pulse mt-1">
                                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> AO VIVO
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>

                                    <div class="flex flex-col items-center gap-2 flex-1">
                                        <a href="<?php echo $link_fora; ?>">
                                            <img src="<?php echo $escudo_fora; ?>" class="w-14 h-14 object-contain bg-white rounded-full p-1 shadow-lg group-hover:scale-110 transition-transform">
                                        </a>
                                        <a href="<?php echo $link_fora; ?>" class="text-xs font-black text-center uppercase tracking-tighter hover:text-laranja transition-colors"><?php echo $nome_fora; ?></a>
                                    </div>
                                </div>
                            </div>

                            <!-- LOCAL E HORA -->
                            <div class="bg-slate-950/50 rounded-2xl p-4 mb-6 space-y-2">
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span class="text-gray-500 uppercase"><i class="far fa-calendar-alt text-laranja mr-1"></i> Data</span>
                                    <span class="text-white"><?php echo date('d/m/Y', strtotime($jogo['data'])); ?></span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span class="text-gray-500 uppercase"><i class="far fa-clock text-laranja mr-1"></i> Horário</span>
                                    <span class="text-white"><?php echo $jogo['horario']; ?></span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span class="text-gray-500 uppercase"><i class="fas fa-map-marker-alt text-laranja mr-1"></i> Cidade</span>
                                    <span class="text-white"><?php echo $jogo['cidade']; ?></span>
                                </div>
                            </div>

                            <!-- TRANSMISSÃO -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tv text-laranja text-[10px]"></i>
                                    <span class="text-[9px] font-black text-gray-400 uppercase"><?php echo $jogo['onde']; ?></span>
                                </div>
                                <a href="<?php echo $jogo['link']; ?>" class="bg-white text-black px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-wider hover:bg-laranja hover:text-white transition-all">Ver Detalhes</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php get_footer(); ?>

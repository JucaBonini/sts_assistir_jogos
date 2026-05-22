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
                
                // Buscar sigla personalizada ou usar fallback de 3 letras
                $sigla = $data['id'] ? get_post_meta($data['id'], 'sigla_selecao', true) : '';
                if (!$sigla) {
                    $sigla = mb_strtoupper(mb_substr($nome, 0, 3));
                }
                
                $grupos_data[$grp][$nome] = array(
                    'P' => 0, 'J' => 0, 'V' => 0, 'E' => 0, 'D' => 0, 'GP' => 0, 'GC' => 0, 'SG' => 0,
                    'escudo' => $escudo_final,
                    'link' => $data['link'],
                    'sigla' => $sigla
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
                        Saiba mais sobre o jogo <i class="fas fa-play"></i>
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
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <?php foreach ($grupos_data as $nome_grupo => $times) : ?>
                        <div class="glass-card rounded-3xl overflow-hidden transition-all duration-300">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-[11px] copa-font border-collapse">
                                    <thead>
                                        <tr class="text-gray-400 uppercase text-[9px] border-b border-slate-800/50 bg-slate-950/30">
                                            <th class="px-4 py-3 font-black text-white text-left tracking-wider"><?php echo $nome_grupo; ?></th>
                                            <th class="px-1.5 py-3 text-center w-8 font-bold">J</th>
                                            <th class="px-1.5 py-3 text-center w-8 font-bold">C</th>
                                            <th class="px-1.5 py-3 text-center w-8 font-bold">E</th>
                                            <th class="px-1.5 py-3 text-center w-8 font-bold">D</th>
                                            <th class="px-2 py-3 text-center w-10 font-bold">DG</th>
                                            <th class="px-2 py-3 text-center w-10 font-black text-white bg-laranja/10">Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $pos = 1;
                                        foreach ($times as $nome_time => $stats) : 
                                            $bg_pos = ($pos <= 2) ? 'bg-emerald-500/5' : '';
                                            $bar_color = ($pos <= 2) ? 'bg-emerald-500' : 'bg-slate-800';
                                        ?>
                                            <tr class="border-b border-slate-800/30 hover:bg-slate-800/40 transition-colors <?php echo $bg_pos; ?>">
                                                <td class="px-4 py-3 flex items-center gap-2.5">
                                                    <!-- Barra de classificação verde/cinza -->
                                                    <span class="w-0.5 h-4 rounded-full <?php echo $bar_color; ?>"></span>
                                                    <span class="w-3 text-[10px] text-gray-400 font-black"><?php echo $pos; ?></span>
                                                    <img src="<?php echo $stats['escudo']; ?>" class="w-5 h-3.5 object-cover rounded shadow-sm flex-shrink-0">
                                                    <a href="<?php echo $stats['link']; ?>" class="font-black text-white hover:text-laranja transition-colors tracking-wide uppercase text-[10px]" title="<?php echo esc_attr($nome_time); ?>">
                                                        <?php echo esc_html($stats['sigla']); ?>
                                                    </a>
                                                </td>
                                                <td class="px-1.5 py-3 text-center text-gray-400 font-bold"><?php echo $stats['J']; ?></td>
                                                <td class="px-1.5 py-3 text-center text-gray-400"><?php echo $stats['V']; ?></td>
                                                <td class="px-1.5 py-3 text-center text-gray-500"><?php echo $stats['E']; ?></td>
                                                <td class="px-1.5 py-3 text-center text-gray-500"><?php echo $stats['D']; ?></td>
                                                <td class="px-2 py-3 text-center font-bold <?php echo ($stats['SG'] > 0) ? 'text-green-500' : ($stats['SG'] < 0 ? 'text-red-500' : 'text-gray-500'); ?>">
                                                    <?php echo ($stats['SG'] > 0 ? '+' : '') . $stats['SG']; ?>
                                                </td>
                                                <td class="px-2 py-3 text-center font-black text-white bg-laranja/5"><?php echo $stats['P']; ?></td>
                                            </tr>
                                            <?php $pos++; ?>
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
        <!-- GUIA INTERATIVO DE CONVOCADOS -->
        <?php
        // Buscar todas as seleções ordenadas por nome
        $selecoes_convocados = get_posts(array(
            'post_type' => 'selecao',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        $jogadores_por_selecao = array();
        if (!empty($selecoes_convocados)) {
            foreach ($selecoes_convocados as $sel) {
                $sel_id = $sel->ID;
                $players = get_posts(array(
                    'post_type' => 'jogador',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'selecao_vinculada',
                            'value' => $sel_id,
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'meta_value_num',
                    'meta_key' => 'numero_camisa',
                    'order' => 'ASC'
                ));
                
                // Group players by position
                $grouped = array(
                    'Goleiros' => array(),
                    'Defensores' => array(),
                    'Meio-campistas' => array(),
                    'Atacantes' => array()
                );
                
                foreach ($players as $p) {
                    $posicao = get_post_meta($p->ID, 'posicao', true) ?: 'Jogador';
                    $posicao_clean = mb_strtolower($posicao);
                    
                    // Smart categorization
                    if (strpos($posicao_clean, 'goleir') !== false || strpos($posicao_clean, 'gk') !== false || strpos($posicao_clean, 'arquero') !== false) {
                        $category = 'Goleiros';
                    } elseif (strpos($posicao_clean, 'defes') !== false || strpos($posicao_clean, 'zagueir') !== false || strpos($posicao_clean, 'lateral') !== false || strpos($posicao_clean, 'zaga') !== false || strpos($posicao_clean, 'backer') !== false || strpos($posicao_clean, 'back') !== false) {
                        $category = 'Defensores';
                    } elseif (strpos($posicao_clean, 'meia') !== false || strpos($posicao_clean, 'volante') !== false || strpos($posicao_clean, 'medio') !== false || strpos($posicao_clean, 'campista') !== false || strpos($posicao_clean, 'midfield') !== false) {
                        $category = 'Meio-campistas';
                    } else {
                        $category = 'Atacantes';
                    }
                    
                    $numero = get_post_meta($p->ID, 'numero_camisa', true) ?: '0';
                    $clube = get_post_meta($p->ID, 'clube_atual', true) ?: 'Sem clube';
                    $foto = get_the_post_thumbnail_url($p->ID, 'thumbnail') ?: '';
                    
                    $grouped[$category][] = array(
                        'id' => $p->ID,
                        'nome' => $p->post_title,
                        'posicao' => $posicao,
                        'numero' => $numero,
                        'clube' => $clube,
                        'foto' => $foto
                    );
                }
                
                $jogadores_por_selecao[$sel_id] = array(
                    'nome' => $sel->post_title,
                    'sigla' => get_post_meta($sel_id, 'sigla_selecao', true) ?: mb_strtoupper(mb_substr($sel->post_title, 0, 3)),
                    'tecnico' => get_post_meta($sel_id, 'tecnico', true) ?: 'A definir',
                    'ranking' => get_post_meta($sel_id, 'ranking_fifa', true) ?: '--',
                    'grupo' => get_post_meta($sel_id, 'grupo_selecao', true) ?: '--',
                    'bandeira' => get_the_post_thumbnail_url($sel_id, 'medium') ?: '',
                    'link' => get_permalink($sel_id),
                    'elenco' => $grouped
                );
            }
        }
        ?>

        <section class="mb-20 bg-slate-900/20 border border-slate-800 rounded-3xl p-6 md:p-8" id="squads-hub">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 border-b border-slate-800 pb-6">
                <div>
                    <span class="inline-block bg-laranja text-white text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider mb-2">Central Exclusiva</span>
                    <h2 class="text-2xl md:text-3xl font-black uppercase italic tracking-tight text-white">Guia de <span class="text-laranja">Convocados Oficiais</span></h2>
                </div>
                
                <!-- Search bar -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-500"></i>
                    </span>
                    <input type="text" id="player-search" placeholder="Pesquisar jogador ou seleção..." class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-2xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-laranja/50 focus:ring-1 focus:ring-laranja/50 transition">
                </div>
            </div>

            <?php if (empty($jogadores_por_selecao)) : ?>
                <div class="bg-slate-900/30 border border-slate-800 rounded-2xl p-6 text-center text-xs text-gray-600 italic">
                    Cadastre as seleções participantes no menu "Seleções" para que elas apareçam aqui.
                </div>
            <?php else : ?>
                <!-- Selection Grid/Grid de Seleções -->
                <div class="mb-8">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3">Escolha uma Seleção para ver os Convocados:</p>
                    <div class="grid grid-cols-2 xs:grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3 max-h-48 overflow-y-auto pr-2" id="teams-selector" style="scrollbar-width: thin; scrollbar-color: rgba(249,115,22,0.4) transparent;">
                        <?php 
                        $first_sel_id = null;
                        $index = 0;
                        foreach ($jogadores_por_selecao as $sel_id => $data) : 
                            if ($index === 0) $first_sel_id = $sel_id;
                            $active_class = ($index === 0) ? 'border-laranja bg-laranja/10 text-white' : 'border-slate-800 bg-slate-950/30 text-gray-400 hover:border-slate-700 hover:text-white';
                        ?>
                            <button data-team-id="<?php echo $sel_id; ?>" class="team-btn flex items-center justify-center gap-2 p-2.5 rounded-xl border <?php echo $active_class; ?> text-xs font-black transition-all duration-300">
                                <?php if ($data['bandeira']) : ?>
                                    <img src="<?php echo $data['bandeira']; ?>" class="w-5 h-3.5 object-cover rounded shadow-sm flex-shrink-0">
                                <?php endif; ?>
                                <span class="truncate uppercase"><?php echo esc_html($data['nome']); ?></span>
                            </button>
                        <?php $index++; endforeach; ?>
                    </div>
                </div>

                <!-- Squad Panels (Container de Elencos) -->
                <div id="squad-panels-container" class="relative min-h-[300px]">
                    
                    <!-- SEARCH RESULTS (Hidden by default) -->
                    <div id="search-results-panel" class="hidden">
                        <h3 class="text-sm font-black uppercase text-laranja mb-6 flex items-center gap-2">
                            <i class="fas fa-search"></i> Resultados da Busca
                        </h3>
                        <div id="search-results-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <!-- JavaScript will populate this -->
                        </div>
                        <div id="search-results-empty" class="hidden text-center py-12 text-gray-500 italic">
                            Nenhum jogador encontrado com este nome.
                        </div>
                    </div>

                    <!-- INDIVIDUAL TEAMS PANELS -->
                    <?php 
                    $index = 0;
                    foreach ($jogadores_por_selecao as $sel_id => $data) : 
                        $active_panel_class = ($index === 0) ? '' : 'hidden';
                    ?>
                        <div id="panel-team-<?php echo $sel_id; ?>" class="team-squad-panel <?php echo $active_panel_class; ?> transition-all duration-300">
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                                
                                <!-- Left: Seleção Bio Card -->
                                <div class="lg:col-span-1 flex flex-col">
                                    <div class="glass-card rounded-3xl p-6 border border-slate-800 flex flex-col items-center text-center h-full">
                                        <?php if ($data['bandeira']) : ?>
                                            <img src="<?php echo $data['bandeira']; ?>" class="w-32 h-20 object-cover rounded-2xl shadow-xl border border-white/10 mb-4 transform hover:scale-105 transition duration-500">
                                        <?php endif; ?>
                                        <h3 class="text-2xl font-black uppercase tracking-tighter text-white mb-2"><?php echo esc_html($data['nome']); ?></h3>
                                        <span class="bg-laranja/10 text-laranja text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider mb-6 border border-laranja/10">
                                            Grupo <?php echo esc_html($data['grupo']); ?>
                                        </span>

                                        <div class="w-full space-y-4 mb-6 border-t border-b border-slate-800/80 py-4 text-left">
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-500 font-bold uppercase">Técnico:</span>
                                                <span class="text-white font-black"><?php echo esc_html($data['tecnico']); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-500 font-bold uppercase">Ranking FIFA:</span>
                                                <span class="text-white font-black">#<?php echo esc_html($data['ranking']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <a href="<?php echo esc_url($data['link']); ?>" class="mt-auto w-full py-3 bg-white/5 hover:bg-laranja text-gray-300 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider text-center transition-all duration-300 border border-white/5">
                                            Perfil Completo
                                        </a>
                                    </div>
                                </div>

                                <!-- Right: Players by Position -->
                                <div class="lg:col-span-3 space-y-8">
                                    <?php 
                                    $has_players = false;
                                    foreach ($data['elenco'] as $position_name => $players_list) {
                                        if (!empty($players_list)) $has_players = true;
                                    }
                                    
                                    if (!$has_players) : 
                                    ?>
                                        <div class="flex flex-col items-center justify-center py-20 text-center text-gray-500 italic bg-slate-950/20 border border-slate-800 rounded-3xl h-full">
                                            <i class="fas fa-users text-4xl text-slate-800 mb-3"></i>
                                            Nenhum jogador cadastrado para esta seleção ainda.
                                        </div>
                                    <?php else : ?>
                                        <?php foreach ($data['elenco'] as $position_name => $players_list) : 
                                            if (empty($players_list)) continue;
                                        ?>
                                            <div>
                                                <h4 class="text-xs font-black uppercase text-laranja tracking-wider mb-4 flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 bg-laranja rounded-full"></span>
                                                    <?php echo $position_name; ?>
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                                    <?php foreach ($players_list as $player) : ?>
                                                        <div class="player-card glass-card p-3 rounded-2xl flex items-center gap-3 border border-slate-800/80 hover:border-laranja/30 transition-all duration-300 group" data-player-name="<?php echo esc_attr(mb_strtolower($player['nome'])); ?>" data-player-team="<?php echo esc_attr(mb_strtolower($data['nome'])); ?>">
                                                            <div class="relative flex-shrink-0">
                                                                <?php if ($player['foto']) : ?>
                                                                    <img src="<?php echo $player['foto']; ?>" class="w-11 h-11 object-cover rounded-xl bg-slate-900 border border-slate-800">
                                                                <?php else : ?>
                                                                    <div class="w-11 h-11 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-center text-gray-600">
                                                                        <i class="fas fa-tshirt text-xs"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <span class="absolute -top-1.5 -left-1.5 bg-laranja text-white text-[8px] font-black w-4.5 h-4.5 flex items-center justify-center rounded-md shadow-lg"><?php echo $player['numero']; ?></span>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <h5 class="font-black text-white text-xs truncate group-hover:text-laranja transition-colors uppercase tracking-tight"><?php echo esc_html($player['nome']); ?></h5>
                                                                <div class="flex justify-between items-center text-[9px] text-gray-500 font-bold uppercase mt-0.5">
                                                                    <span class="truncate max-w-[90px]"><?php echo esc_html($player['clube']); ?></span>
                                                                    <span class="text-laranja/65"><?php echo esc_html($player['posicao']); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php $index++; endforeach; ?>

                </div>
            <?php endif; ?>
        </section>

        <!-- SCRIPT DO GUIA INTERATIVO -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const teamButtons = document.querySelectorAll('.team-btn');
            const squadPanels = document.querySelectorAll('.team-squad-panel');
            const searchInput = document.getElementById('player-search');
            const searchResultsPanel = document.getElementById('search-results-panel');
            const searchResultsGrid = document.getElementById('search-results-grid');
            const searchResultsEmpty = document.getElementById('search-results-empty');

            if (!teamButtons.length) return;

            // Lógica de alternância de abas
            teamButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Se houver texto na busca, limpa
                    if (searchInput.value.trim() !== '') {
                        searchInput.value = '';
                        searchResultsPanel.classList.add('hidden');
                    }

                    const targetTeamId = this.getAttribute('data-team-id');

                    // Desativar todos os botões
                    teamButtons.forEach(btn => {
                        btn.classList.remove('border-laranja', 'bg-laranja/10', 'text-white');
                        btn.classList.add('border-slate-800', 'bg-slate-950/30', 'text-gray-400');
                    });

                    // Ativar o botão clicado
                    this.classList.add('border-laranja', 'bg-laranja/10', 'text-white');
                    this.classList.remove('border-slate-800', 'bg-slate-950/30', 'text-gray-400');

                    // Ocultar todos os painéis
                    squadPanels.forEach(panel => panel.classList.add('hidden'));

                    // Mostrar o painel alvo
                    const activePanel = document.getElementById('panel-team-' + targetTeamId);
                    if (activePanel) {
                        activePanel.classList.remove('hidden');
                    }
                });
            });

            // Lógica de busca em tempo real
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();

                if (query.length < 2) {
                    // Ocultar resultados de busca, restaurar painel ativo
                    searchResultsPanel.classList.add('hidden');
                    
                    const activeBtn = document.querySelector('.team-btn.border-laranja');
                    if (activeBtn) {
                        const activeTeamId = activeBtn.getAttribute('data-team-id');
                        squadPanels.forEach(panel => {
                            if (panel.id === 'panel-team-' + activeTeamId) {
                                panel.classList.remove('hidden');
                            } else {
                                panel.classList.add('hidden');
                            }
                        });
                    }
                    return;
                }

                // Ocultar todos os painéis padrão de equipes
                squadPanels.forEach(panel => panel.classList.add('hidden'));

                // Limpar resultados anteriores
                searchResultsGrid.innerHTML = '';
                searchResultsPanel.classList.remove('hidden');

                // Buscar jogadores que correspondem à consulta
                const allPlayerCards = [];
                const seenPlayers = new Set();

                const playerSourceCards = document.querySelectorAll('.team-squad-panel .player-card');
                
                playerSourceCards.forEach(card => {
                    const playerName = card.getAttribute('data-player-name') || '';
                    const playerTeam = card.getAttribute('data-player-team') || '';
                    
                    if (playerName.includes(query) || playerTeam.includes(query)) {
                        // Clonar o card do jogador
                        const clone = card.cloneNode(true);
                        clone.classList.remove('border-slate-800/80');
                        clone.classList.add('border-slate-800');
                        
                        // Inserir tag com o nome do país no card clonado
                        const teamNameRaw = card.getAttribute('data-player-team').toUpperCase();
                        const infoDiv = clone.querySelector('.flex-1');
                        if (infoDiv) {
                            const countryBadge = document.createElement('span');
                            countryBadge.className = 'inline-block mt-1 text-[8px] bg-white/5 text-gray-400 font-bold px-1.5 py-0.5 rounded uppercase';
                            countryBadge.innerText = teamNameRaw;
                            infoDiv.appendChild(countryBadge);
                        }

                        const key = playerName + '_' + playerTeam;
                        if (!seenPlayers.has(key)) {
                            seenPlayers.add(key);
                            allPlayerCards.push(clone);
                        }
                    }
                });

                if (allPlayerCards.length > 0) {
                    searchResultsEmpty.classList.add('hidden');
                    searchResultsGrid.classList.remove('hidden');
                    allPlayerCards.forEach(card => searchResultsGrid.appendChild(card));
                } else {
                    searchResultsGrid.classList.add('hidden');
                    searchResultsEmpty.classList.remove('hidden');
                }
            });
        });
        </script>

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

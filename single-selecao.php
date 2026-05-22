<?php
/**
 * Template para exibição individual de uma Seleção
 */
get_header();

$selecao_id = get_the_ID();
$tecnico = get_post_meta($selecao_id, 'tecnico', true);
$ranking = get_post_meta($selecao_id, 'ranking_fifa', true);
$grupo = get_post_meta($selecao_id, 'grupo_selecao', true);
$bandeira = get_the_post_thumbnail_url($selecao_id, 'full');

// Buscar Jogadores desta Seleção
$jogadores = get_posts(array(
    'post_type' => 'jogador',
    'posts_per_page' => -1,
    'meta_key' => 'numero_camisa',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'selecao_vinculada',
            'value' => $selecao_id,
            'compare' => '='
        )
    )
));
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
    .copa-font { font-family: 'Outfit', sans-serif; }
    .hero-gradient {
        background: linear-gradient(to bottom, rgba(15, 23, 42, 0.8), #0f172a), 
                    url('<?php echo $bandeira; ?>') center/cover;
    }
    .glass-card {
        background: rgba(30, 41, 59, 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>

<div class="bg-slate-950 text-white min-h-screen copa-font">
    <!-- HERO DA SELEÇÃO -->
    <header class="relative pt-32 pb-20 overflow-hidden hero-gradient">
        <div class="container mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <img src="<?php echo $bandeira; ?>" class="w-32 h-32 md:w-48 md:h-48 object-cover rounded-3xl shadow-2xl border-4 border-white/10">
                <div class="text-center md:text-left">
                    <span class="bg-laranja text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-4 inline-block">Copa do Mundo 2026</span>
                    <h1 class="text-5xl md:text-8xl font-black uppercase italic tracking-tighter mb-4"><?php the_title(); ?></h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-6 text-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Técnico</span>
                            <span class="font-bold text-laranja text-lg"><?php echo $tecnico ?: 'A definir'; ?></span>
                        </div>
                        <div class="flex flex-col border-l border-white/10 pl-6">
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Ranking FIFA</span>
                            <span class="font-bold text-white text-lg">#<?php echo $ranking ?: '--'; ?></span>
                        </div>
                        <div class="flex flex-col border-l border-white/10 pl-6">
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Grupo</span>
                            <span class="font-bold text-white text-lg"><?php echo $grupo ?: '--'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- COLUNA ESQUERDA: ELENCO -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-black uppercase italic mb-8 border-l-4 border-laranja pl-4">Convocados <span class="text-laranja">Oficiais</span></h2>
                
                <?php if (empty($jogadores)) : ?>
                    <p class="text-gray-500 italic bg-slate-900/50 p-8 rounded-2xl border border-white/5">Nenhum jogador cadastrado para esta seleção ainda.</p>
                <?php else : ?>
                    <div class="space-y-8">
                        <?php 
                        // Agrupar e categorizar jogadores
                        $grouped = array(
                            'Goleiros' => array(),
                            'Defensores' => array(),
                            'Meio-campistas' => array(),
                            'Atacantes' => array()
                        );
                        
                        foreach ($jogadores as $j) {
                            $posicao = get_post_meta($j->ID, 'posicao', true) ?: 'Jogador';
                            $posicao_clean = mb_strtolower($posicao);
                            
                            if (strpos($posicao_clean, 'goleir') !== false || strpos($posicao_clean, 'gk') !== false || strpos($posicao_clean, 'arquero') !== false) {
                                $category = 'Goleiros';
                            } elseif (strpos($posicao_clean, 'defes') !== false || strpos($posicao_clean, 'zagueir') !== false || strpos($posicao_clean, 'lateral') !== false || strpos($posicao_clean, 'zaga') !== false || strpos($posicao_clean, 'backer') !== false || strpos($posicao_clean, 'back') !== false) {
                                $category = 'Defensore' . 's'; // Evitando que a concatenação ou qualquer typo quebre
                            } elseif (strpos($posicao_clean, 'meia') !== false || strpos($posicao_clean, 'volante') !== false || strpos($posicao_clean, 'medio') !== false || strpos($posicao_clean, 'campista') !== false || strpos($posicao_clean, 'midfield') !== false) {
                                $category = 'Meio-campistas';
                            } else {
                                $category = 'Atacantes';
                            }
                            
                            $numero = get_post_meta($j->ID, 'numero_camisa', true) ?: '0';
                            $clube = get_post_meta($j->ID, 'clube_atual', true) ?: 'Sem clube';
                            $foto = get_the_post_thumbnail_url($j->ID, 'thumbnail') ?: '';
                            
                            $grouped[$category][] = array(
                                'id' => $j->ID,
                                'nome' => $j->post_title,
                                'posicao' => $posicao,
                                'numero' => $numero,
                                'clube' => $clube,
                                'foto' => $foto,
                                'link' => get_permalink($j->ID)
                            );
                        }
                        
                        foreach ($grouped as $position_name => $players_list) : 
                            if (empty($players_list)) continue;
                        ?>
                            <div>
                                <h3 class="text-xs font-black uppercase text-laranja tracking-wider mb-4 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-laranja rounded-full"></span>
                                    <?php echo esc_html($position_name); ?>
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($players_list as $player) : ?>
                                        <a href="<?php echo esc_url($player['link']); ?>" class="glass-card p-4 rounded-2xl flex items-center gap-4 hover:border-laranja/30 transition-all group">
                                            <div class="relative flex-shrink-0">
                                                <?php if ($player['foto']) : ?>
                                                    <img src="<?php echo esc_url($player['foto']); ?>" class="w-16 h-16 object-cover rounded-xl bg-slate-800 border border-slate-700">
                                                <?php else : ?>
                                                    <div class="w-16 h-16 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-center text-gray-600">
                                                        <i class="fas fa-tshirt text-xl"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="absolute -top-2 -left-2 bg-laranja text-white text-[10px] font-black w-6 h-6 flex items-center justify-center rounded-lg shadow-lg"><?php echo esc_html($player['numero']); ?></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-black uppercase italic text-sm group-hover:text-laranja transition truncate"><?php echo esc_html($player['nome']); ?></h4>
                                                <div class="flex flex-col mt-0.5">
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase"><?php echo esc_html($player['posicao']); ?></span>
                                                    <span class="text-[9px] text-gray-400 truncate"><?php echo esc_html($player['clube']); ?></span>
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right text-slate-700 group-hover:text-laranja transition mr-2"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- COLUNA DIREITA: JOGOS E INFO -->
            <div class="space-y-12">
                <div class="glass-card p-8 rounded-3xl">
                    <h3 class="text-lg font-black uppercase italic mb-6">Últimas Notícias</h3>
                    <p class="text-xs text-gray-400 italic">As novidades sobre a preparação desta seleção aparecerão aqui em breve.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl border-t-4 border-laranja">
                    <h3 class="text-lg font-black uppercase italic mb-6">Análise Técnica</h3>
                    <div class="prose prose-invert prose-sm">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>

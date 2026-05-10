<?php
/**
 * Template para exibição individual de um Jogador
 */
get_header();

$jogador_id = get_the_ID();
$selecao_id = get_post_meta($jogador_id, 'selecao_vinculada', true);
$posicao = get_post_meta($jogador_id, 'posicao', true);
$numero = get_post_meta($jogador_id, 'numero_camisa', true);
$clube = get_post_meta($jogador_id, 'clube_atual', true);
$foto = get_the_post_thumbnail_url($jogador_id, 'full');
$nome_selecao = $selecao_id ? get_the_title($selecao_id) : 'Independente';
$link_selecao = $selecao_id ? get_permalink($selecao_id) : '#';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
    .copa-font { font-family: 'Outfit', sans-serif; }
    .player-gradient {
        background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.1), transparent),
                    linear-gradient(to bottom, #0f172a, #020617);
    }
    .glass-card {
        background: rgba(30, 41, 59, 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>

<div class="bg-slate-950 text-white min-h-screen copa-font player-gradient">
    <div class="container mx-auto px-4 pt-32 pb-20">
        <div class="flex flex-col md:flex-row gap-12 items-center md:items-start">
            
            <!-- FOTO DO JOGADOR -->
            <div class="w-full md:w-1/3 max-w-sm">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-laranja to-yellow-500 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                    <img src="<?php echo $foto ?: get_template_directory_uri().'/assets/images/imagens-post.webp'; ?>" class="relative w-full aspect-[3/4] object-cover rounded-3xl border border-white/10 shadow-2xl">
                    <span class="absolute top-6 right-6 bg-laranja text-white text-3xl font-black w-16 h-16 flex items-center justify-center rounded-2xl shadow-2xl"><?php echo $numero; ?></span>
                </div>
            </div>

            <!-- INFOS -->
            <div class="flex-1 text-center md:text-left">
                <nav class="mb-6 flex justify-center md:justify-start gap-2">
                    <a href="<?php echo home_url('/copa-hub/'); ?>" class="text-[10px] font-black uppercase text-gray-500 hover:text-laranja">Copa 2026</a>
                    <span class="text-gray-700">/</span>
                    <a href="<?php echo $link_selecao; ?>" class="text-[10px] font-black uppercase text-laranja hover:underline"><?php echo $nome_selecao; ?></a>
                </nav>

                <h1 class="text-5xl md:text-7xl font-black uppercase italic tracking-tighter mb-2"><?php the_title(); ?></h1>
                <p class="text-xl text-gray-400 font-medium mb-8"><?php echo $posicao; ?> • <?php echo $clube; ?></p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="glass-card p-6 rounded-2xl text-center">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Seleção</span>
                        <span class="font-black text-white"><?php echo $nome_selecao; ?></span>
                    </div>
                    <div class="glass-card p-6 rounded-2xl text-center">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Gols na Copa</span>
                        <span class="font-black text-laranja text-2xl">0</span>
                    </div>
                    <div class="glass-card p-6 rounded-2xl text-center">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Assistências</span>
                        <span class="font-black text-white text-2xl">0</span>
                    </div>
                    <div class="glass-card p-6 rounded-2xl text-center">
                        <span class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nota Média</span>
                        <span class="font-black text-white text-2xl">--</span>
                    </div>
                </div>

                <div class="mt-12">
                    <h3 class="text-sm font-black uppercase italic text-gray-500 mb-4 border-l-2 border-laranja pl-3">Perfil do Atleta</h3>
                    <div class="prose prose-invert prose-sm max-w-none">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

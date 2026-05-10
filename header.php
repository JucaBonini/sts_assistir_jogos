<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="max-image-preview:large">
    <meta name="theme-color" content="#E67E22">
    <?php
    // SEO dinâmico sem plugin
    if (is_singular()) {
        $desc = wp_strip_all_tags(get_the_excerpt()) ?: wp_trim_words(get_the_content(), 25);
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        
        // Open Graph
        echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="' . get_permalink() . '">' . "\n";
        if (has_post_thumbnail()) {
            echo '<meta property="og:image" content="' . get_the_post_thumbnail_url(get_the_ID(), 'full') . '">' . "\n";
        }
    }
    ?>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/logotipo.jpg">
    <?php if ( ! has_site_icon() ) : ?>
        <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/logtipo_2.webp" sizes="32x32">
    <?php endif; ?>
    <?php wp_head(); ?>
    
    <!-- Customização da paleta (Tailwind Config) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'laranja': '#E67E22',
                        'verde-menta': '#27AE60',
                        'azul-confianca': '#2C3E50',
                        'card-bg': '#1E293B',
                        'bg-escuro': '#0F172A',
                    }
                }
            }
        }
    </script>
    <style>
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.3); }
        .btn-filter.active { background-color: #E67E22; color: white; border-color: #E67E22; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0F172A; }
        ::-webkit-scrollbar-thumb { background: #E67E22; border-radius: 10px; }
    </style>
</head>
<body <?php body_class('bg-bg-escuro text-gray-200 font-sans antialiased'); ?>>

    <header class="sticky top-0 z-50 bg-slate-900/90 backdrop-blur-md border-b border-slate-700">
        <div class="container mx-auto px-4 py-3 flex flex-wrap items-center justify-between">
            <!-- Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center">
                <!-- Logo Desktop -->
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logotipo_assistir_jogos.webp" alt="Assistir Jogos" class="hidden md:block h-12 w-auto object-contain">
                <!-- Logo Mobile -->
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logotipo_assistir_jogos_mobile.webp" alt="Assistir Jogos" class="block md:hidden h-12 w-auto object-contain">
            </a>
            
            <!-- Menu Desktop -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="<?php echo home_url(); ?>" class="hover:text-laranja transition">Hoje</a>
                
                <!-- Dropdown Esportes -->
                <div class="relative group">
                    <button class="hover:text-laranja transition flex items-center gap-1">
                        Esportes <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-2">
                            <?php
                            $esportes = get_terms(array('taxonomy' => 'esporte', 'hide_empty' => true));
                            foreach ($esportes as $esporte) :
                            ?>
                                <a href="<?php echo get_term_link($esporte); ?>" class="block px-4 py-2 hover:bg-laranja hover:text-white rounded-lg transition">
                                    <?php echo esc_html($esporte->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Canais -->
                <div class="relative group">
                    <button class="hover:text-laranja transition flex items-center gap-1">
                        Canais <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                    <div class="absolute left-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-2">
                            <?php
                            $canais = get_terms(array('taxonomy' => 'canal', 'hide_empty' => true));
                            foreach ($canais as $canal) :
                            ?>
                                <a href="<?php echo get_term_link($canal); ?>" class="block px-4 py-2 hover:bg-laranja hover:text-white rounded-lg transition">
                                    <?php echo esc_html($canal->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <a href="<?php echo home_url('/copa-do-mundo'); ?>" class="text-laranja font-black flex items-center gap-2 group hover:scale-105 transition-transform">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-laranja opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-laranja"></span>
                    </span>
                    COPA 2026
                </a>
                <a href="<?php echo home_url('/blog'); ?>" class="hover:text-laranja transition">Notícias</a>
            </nav>
            
            <!-- Mobile menu button -->
            <button id="menu-btn" class="md:hidden text-gray-300 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-slate-800 border-t border-slate-700 px-4 py-3 flex flex-col space-y-3 text-sm">
            <a href="<?php echo home_url('/copa-do-mundo'); ?>" class="text-laranja font-black flex items-center gap-2 py-2 border-b border-white/5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-laranja opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-laranja"></span>
                </span>
                COPA DO MUNDO 2026
            </a>
            <a href="<?php echo home_url(); ?>" class="hover:text-laranja">Hoje</a>
            <div class="text-gray-500 font-bold text-[10px] uppercase tracking-widest pt-2">Esportes</div>
            <?php
            foreach ($esportes as $esporte) :
            ?>
                <a href="<?php echo get_term_link($esporte); ?>" class="hover:text-laranja pl-2 border-l border-slate-600">
                    <?php echo esc_html($esporte->name); ?>
                </a>
            <?php endforeach; ?>
            <div class="text-gray-500 font-bold text-[10px] uppercase tracking-widest pt-2">Canais</div>
            <?php
            foreach ($canais as $canal) :
            ?>
                <a href="<?php echo get_term_link($canal); ?>" class="hover:text-laranja pl-2 border-l border-slate-600">
                    <?php echo esc_html($canal->name); ?>
                </a>
            <?php endforeach; ?>

            <a href="<?php echo home_url('#jogos-hoje'); ?>" class="hover:text-laranja pt-2">Jogos de Hoje</a>
            <a href="<?php echo home_url('/blog'); ?>" class="hover:text-laranja pt-2">Notícias e Artigos</a>
        </div>
    </header>

    <?php if (function_exists('custom_breadcrumbs')) custom_breadcrumbs(); ?>

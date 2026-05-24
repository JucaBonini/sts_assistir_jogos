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

    <!-- Google Consent Mode v2 Default Settings (LGPD / ECA Digital) -->
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        
        var savedConsent = localStorage.getItem('lgpd_cookie_consent');
        var consentState = savedConsent === 'accepted' ? 'granted' : 'denied';

        gtag('consent', 'default', {
            'ad_storage': consentState,
            'ad_user_data': consentState,
            'ad_personalization': consentState,
            'analytics_storage': consentState,
            'wait_for_update': 500
        });

        if (consentState === 'denied') {
            window.adsbygoogle = window.adsbygoogle || [];
            window.adsbygoogle.requestNonPersonalizedAds = 1;
        }
    </script>

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
        
        /* Custom scrollbar for dropdowns */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E67E22; border-radius: 10px; }

        /* Animation for nested dropdown */
        .group-hover\/sub\:visible { transition-delay: 0s; }
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
                                $is_futebol = (trim(strtolower($esporte->name)) == 'futebol');
                            ?>
                                <div class="relative group/sub">
                                    <a href="<?php echo get_term_link($esporte); ?>" class="flex items-center justify-between px-4 py-2 hover:bg-laranja hover:text-white rounded-lg transition">
                                        <?php echo esc_html($esporte->name); ?>
                                        <?php if ($is_futebol) : ?>
                                            <i class="fas fa-chevron-right text-[10px] ml-2"></i>
                                        <?php endif; ?>
                                    </a>
                                    
                                    <?php if ($is_futebol) : ?>
                                        <!-- Submenu de Campeonatos (Apenas para Futebol) -->
                                        <div class="absolute left-full top-0 ml-1 w-56 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 z-50">
                                            <div class="p-2 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest px-4 py-1 border-b border-white/5 mb-1">Campeonatos</div>
                                                <?php
                                                $campeonatos = get_terms(array('taxonomy' => 'campeonato', 'hide_empty' => true));
                                                foreach ($campeonatos as $camp) :
                                                ?>
                                                    <a href="<?php echo get_term_link($camp); ?>" class="block px-4 py-2 hover:bg-laranja hover:text-white rounded-lg transition text-xs">
                                                        <?php echo esc_html($camp->name); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
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

                <a href="<?php echo home_url('/brasileirao'); ?>" class="text-verde-menta font-black flex items-center gap-2 group hover:scale-105 transition-transform">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-verde-menta opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-verde-menta"></span>
                    </span>
                    BRASILEIRÃO 2026
                </a>
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
            <a href="<?php echo home_url('/brasileirao'); ?>" class="text-verde-menta font-black flex items-center gap-2 py-2 border-b border-white/5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-verde-menta opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-verde-menta"></span>
                </span>
                BRASILEIRÃO 2026
            </a>
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
                $is_futebol = (trim(strtolower($esporte->name)) == 'futebol');
            ?>
                <div class="flex flex-col">
                    <a href="<?php echo get_term_link($esporte); ?>" class="hover:text-laranja pl-2 border-l border-slate-600 flex justify-between items-center group">
                        <?php echo esc_html($esporte->name); ?>
                        <?php if ($is_futebol) : ?>
                            <i class="fas fa-chevron-down text-[10px] text-gray-500 mr-2"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if ($is_futebol) : ?>
                        <!-- Lista de Campeonatos no Mobile -->
                        <div class="grid grid-cols-1 gap-1 pl-6 mt-1 mb-2 border-l border-slate-700/50">
                            <?php
                            foreach ($campeonatos as $camp) :
                            ?>
                                <a href="<?php echo get_term_link($camp); ?>" class="text-xs text-gray-400 hover:text-laranja py-1">
                                    • <?php echo esc_html($camp->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
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

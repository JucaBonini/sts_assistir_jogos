    <footer class="bg-slate-900 border-t border-slate-800 mt-16 py-8">
        <div class="container mx-auto px-4 text-center text-gray-400 text-sm space-y-3">
            <div class="bg-slate-800/50 rounded-lg p-4 max-w-3xl mx-auto">
                <i class="fas fa-exclamation-triangle text-laranja mr-2"></i> 
                <span class="font-semibold">Este site NÃO transmite jogos.</span> Somos um guia de programação legal e independente. Respeitamos integralmente os direitos de transmissão.
                <br>As informações são obtidas de fontes oficiais (canais de TV, streaming e estabelecimentos parceiros).
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 py-8 border-b border-slate-800 text-left">
                <div class="col-span-1 lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 uppercase text-xs tracking-widest text-laranja">Sobre o Projeto</h4>
                    <p class="text-gray-500 text-xs leading-relaxed max-w-sm">O Assistir Jogos é o seu guia definitivo de programação esportiva. Nossa missão é facilitar o acesso à informação de onde assistir seus jogos favoritos de forma totalmente legal e segura.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-xs tracking-widest text-laranja">Links Úteis</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?php echo home_url('/sobre-nos'); ?>" class="hover:text-white transition">Quem Somos</a></li>
                        <li><a href="<?php echo home_url('/contato'); ?>" class="hover:text-white transition">Fale Conosco</a></li>
                        <li><a href="<?php echo home_url('/blog'); ?>" class="hover:text-white transition">Notícias e Artigos</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-xs tracking-widest text-laranja">Legal</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?php echo home_url('/politica-de-privacidade'); ?>" class="hover:text-white transition">Privacidade</a></li>
                        <li><a href="<?php echo home_url('/termos-de-uso'); ?>" class="hover:text-white transition">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>
            <p class="text-xs text-gray-500">© <?php echo date('Y'); ?> assistirjogos.com.br – Seu guia de onde assistir esportes no Brasil</p>
        </div>
    </footer>

    <!-- BANNER DE COOKIES LGPD & ECA DIGITAL -->
    <div id="lgpd-cookie-banner" class="fixed bottom-4 left-4 right-4 bg-slate-800 border border-slate-700 rounded-3xl p-6 shadow-2xl z-50 transform translate-y-full transition-transform duration-500 hidden md:max-w-md md:left-4">
        <div class="space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-laranja/20 rounded-2xl flex items-center justify-center text-laranja shrink-0">
                    <i class="fas fa-cookie-bite text-2xl animate-pulse"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-bold text-sm leading-tight">Sua Privacidade</h4>
                    <p class="text-gray-400 text-xs mt-2 leading-relaxed font-normal">
                        Usamos cookies para melhorar sua experiência. Em conformidade com a LGPD e o ECA Digital, não exibimos anúncios personalizados para menores. Leia nossa <a href="<?php echo home_url('/politica-de-privacidade'); ?>" class="text-laranja hover:underline font-semibold">Política de Privacidade</a>.
                    </p>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button id="btn-reject-cookies" class="flex-1 bg-slate-750 hover:bg-slate-700 text-gray-300 hover:text-white font-bold py-3 rounded-2xl transition-all duration-200 text-xs uppercase tracking-wider">
                    Recusar
                </button>
                <button id="btn-accept-cookies" class="flex-1 bg-laranja hover:bg-orange-600 text-white font-bold py-3 rounded-2xl transition-all duration-200 text-xs uppercase tracking-wider shadow-lg shadow-laranja/20">
                    Aceitar
                </button>
            </div>
        </div>
    </div>

    <!-- PWA INSTALL BANNER -->
    <div id="pwa-install-banner" class="fixed bottom-4 left-4 right-4 bg-slate-800 border border-laranja/30 rounded-2xl p-4 shadow-2xl transform translate-y-full transition-transform duration-500 z-50 hidden md:max-w-md md:left-auto">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0 bg-slate-900 p-2 rounded-xl border border-slate-700">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logotipo.jpg" alt="Icon" class="w-12 h-12 rounded-lg object-contain">
            </div>
            <div class="flex-1">
                <h4 class="text-white font-bold text-sm leading-tight">Instale nosso App!</h4>
                <p class="text-gray-400 text-xs mt-1">Acompanhe os jogos direto da sua tela inicial.</p>
            </div>
            <button id="close-pwa-banner" class="text-gray-500 hover:text-white p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <button id="btn-install-pwa" class="w-full mt-4 bg-laranja hover:bg-laranja-escura text-white font-bold py-2 rounded-xl transition shadow-lg shadow-laranja/20 text-sm">
            Adicionar à Tela de Início
        </button>
    </div>

    <?php wp_footer(); ?>

    <script>
        // Lógica de Cookies (LGPD & Google Consent Mode)
        const cookieBanner = document.getElementById('lgpd-cookie-banner');
        const btnAccept = document.getElementById('btn-accept-cookies');
        const btnReject = document.getElementById('btn-reject-cookies');

        function setCookieConsent(consent) {
            localStorage.setItem('lgpd_cookie_consent', consent);
            if (typeof gtag === 'function') {
                gtag('consent', 'update', {
                    'ad_storage': consent === 'accepted' ? 'granted' : 'denied',
                    'ad_user_data': consent === 'accepted' ? 'granted' : 'denied',
                    'ad_personalization': consent === 'accepted' ? 'granted' : 'denied',
                    'analytics_storage': consent === 'accepted' ? 'granted' : 'denied'
                });
            }
            if (consent === 'rejected') {
                window.adsbygoogle = window.adsbygoogle || [];
                window.adsbygoogle.requestNonPersonalizedAds = 1;
            }
            if (cookieBanner) {
                cookieBanner.classList.add('translate-y-full');
                setTimeout(() => cookieBanner.classList.add('hidden'), 500);
            }
        }

        window.addEventListener('load', () => {
            const consent = localStorage.getItem('lgpd_cookie_consent');
            if (!consent && cookieBanner) {
                cookieBanner.classList.remove('hidden');
                setTimeout(() => cookieBanner.classList.remove('translate-y-full'), 1000);
            }
        });

        if (btnAccept) {
            btnAccept.addEventListener('click', () => setCookieConsent('accepted'));
        }
        if (btnReject) {
            btnReject.addEventListener('click', () => setCookieConsent('rejected'));
        }

        // Menu Mobile
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Lógica de Favoritos (LocalStorage)
        function salvarTimeFavorito() {
            const input = document.getElementById('input-time-favorito');
            if (input && input.value.trim()) {
                localStorage.setItem('time_favorito', input.value.trim());
                location.reload(); // Recarrega para aplicar ordenação
            }
        }

        function limparTimeFavorito() {
            localStorage.removeItem('time_favorito');
            location.reload();
        }

        // Service Worker PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
                    .then(reg => console.log('PWA Ativo!'))
                    .catch(err => console.log('Erro PWA:', err));
            });
        }

        // Banner de Instalação PWA
        let deferredPrompt;
        const banner = document.getElementById('pwa-install-banner');
        const btnInstall = document.getElementById('btn-install-pwa');
        const btnClose = document.getElementById('close-pwa-banner');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const isClosed = localStorage.getItem('pwa-banner-closed');
            if (!isClosed && banner) {
                banner.classList.remove('hidden');
                setTimeout(() => banner.classList.remove('translate-y-full'), 1000);
            }
        });

        if (btnInstall) {
            btnInstall.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') banner.classList.add('translate-y-full');
                    deferredPrompt = null;
                }
            });
        }

        if (btnClose) {
            btnClose.addEventListener('click', () => {
                banner.classList.add('translate-y-full');
                localStorage.setItem('pwa-banner-closed', Date.now());
            });
        }
    </script>
</body>
</html>

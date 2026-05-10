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
        function renderizarCards(filtro = "todos") {
            const container = document.getElementById("cards-container");
            if (!container) return;
            
            let filtrados = filtro === "todos" ? window.jogosData : window.jogosData.filter(j => j.tipo === filtro);
            
            // LÓGICA DE FAVORITO
            const timeFav = localStorage.getItem('time_favorito') ? localStorage.getItem('time_favorito').toLowerCase().trim() : null;
            
            if (timeFav && filtrados) {
                filtrados.sort((a, b) => {
                    const aFav = (a.timeCasa.toLowerCase().includes(timeFav) || a.timeFora.toLowerCase().includes(timeFav));
                    const bFav = (b.timeCasa.toLowerCase().includes(timeFav) || b.timeFora.toLowerCase().includes(timeFav));
                    return bFav - aFav;
                });
            }

            const agora = new Date();

            // Auto-Limpeza: Filtra jogos que já acabaram (mais de 115 minutos após o início)
            const jogosAtivos = filtrados.filter(jogo => {
                if (!jogo.horario || !jogo.data) return true;
                const [h, m] = jogo.horario.replace('h', ':').split(':');
                const [ano, mes, dia] = jogo.data.split('-');
                const d = new Date(ano, mes - 1, dia, h, m, 0);
                return ((agora - d) / 60000) <= 115;
            });

            if (!jogosAtivos || jogosAtivos.length === 0) {
                container.innerHTML = `<div class="col-span-full text-center py-12 text-gray-400"><i class="fas fa-frown text-4xl mb-3"></i><p>Nenhum jogo disponível no momento.<br>Volte mais tarde ou ajuste o filtro.</p></div>`;
                return;
            }

            container.innerHTML = jogosAtivos.map(jogo => {
                const isFav = timeFav && (jogo.timeCasa.toLowerCase().includes(timeFav) || jogo.timeFora.toLowerCase().includes(timeFav));
                
                // Lógica de badge AO VIVO
                let statusBadge = '';
                if (jogo.horario && jogo.data) {
                    const [h, m] = jogo.horario.replace('h', ':').split(':');
                    const [ano, mes, dia] = jogo.data.split('-');
                    const d = new Date(ano, mes - 1, dia, h, m, 0);
                    const diff = (agora - d) / 60000;
                    if (diff >= -5 && diff <= 115) {
                        statusBadge = '<span class="absolute top-3 right-3 bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded animate-pulse z-20 shadow-lg shadow-red-600/50 flex items-center gap-1"><i class="fas fa-circle text-[6px]"></i> AO VIVO</span>';
                    }
                }

                return `
                <article class="bg-card-bg rounded-2xl shadow-xl overflow-hidden border ${isFav ? 'border-laranja shadow-laranja/10 scale-[1.02]' : 'border-slate-700'} card-hover transition-all group relative">
                    ${statusBadge}
                    ${isFav ? '<div class="absolute top-0 right-0 bg-laranja text-white text-[9px] font-black px-2 py-1 rounded-bl-lg uppercase z-10">⭐ Seu Time</div>' : ''}
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex gap-2">
                                <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-slate-700 text-gray-400 uppercase tracking-wider">${jogo.tipo}</span>
                                ${jogo.campeonato ? `<span class="text-[10px] font-bold px-2 py-1 rounded-md bg-laranja/10 text-laranja uppercase tracking-wider">🏆 ${jogo.campeonato}</span>` : ''}
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full ${jogo.selo === 'GRÁTIS' ? 'bg-green-600/20 text-green-400' : 'bg-yellow-600/20 text-yellow-400'}">${jogo.selo === 'GRÁTIS' ? '📡 GRÁTIS' : '🔒 PAGO'}</span>
                        </div>
                        
                        <a href="${jogo.link}" class="block group-hover:text-laranja transition">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex -space-x-2">
                                    ${jogo.escudoCasa ? `<img src="${jogo.escudoCasa}" alt="Escudo do ${jogo.timeCasa}" class="w-8 h-8 rounded-full border-2 border-slate-800 bg-slate-900 object-contain p-1">` : `<div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-[10px]" aria-label="Escudo genérico casa">🏠</div>`}
                                    ${jogo.escudoFora ? `<img src="${jogo.escudoFora}" alt="Escudo do ${jogo.timeFora}" class="w-8 h-8 rounded-full border-2 border-slate-800 bg-slate-900 object-contain p-1">` : `<div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-[10px]" aria-label="Escudo genérico fora">🚌</div>`}
                                </div>
                                <h3 class="text-lg font-bold text-white leading-tight">${jogo.timeCasa} <span class="text-gray-500 font-normal text-sm">x</span> ${jogo.timeFora}</h3>
                            </div>
                            <div class="flex flex-wrap gap-3 text-sm text-gray-400 mb-4">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-clock text-laranja"></i> 
                                    ${(() => {
                                        const hoje = new Date().toISOString().split('T')[0];
                                        if (jogo.data && jogo.data !== hoje) {
                                            const [ano, mes, dia] = jogo.data.split('-');
                                            return `${dia}/${mes} às `;
                                        }
                                        return '';
                                    })()}${jogo.horario}
                                </span>
                                <span class="flex items-center gap-1"><i class="fas fa-tv text-laranja text-xs"></i> ${jogo.onde}</span>
                            </div>
                        </a>

                        <div class="bg-slate-900/50 rounded-xl p-3 mb-4 border border-slate-700/50">
                            <p class="text-[10px] text-gray-500 uppercase font-bold text-center mb-2 tracking-tighter">Odds de Vitória</p>
                            <div class="flex justify-between gap-1">
                                <button class="flex-1 py-1.5 text-[10px] bg-slate-800 hover:bg-laranja/20 hover:text-laranja border border-slate-700 rounded-lg transition uppercase font-black text-gray-300 flex flex-col items-center">
                                    <span class="text-[8px] text-gray-500 font-bold mb-0.5">Casa</span>
                                    <span class="text-laranja">${jogo.oddCasa}</span>
                                </button>
                                <button class="flex-1 py-1.5 text-[10px] bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg transition uppercase font-black text-gray-300 flex flex-col items-center">
                                    <span class="text-[8px] text-gray-500 font-bold mb-0.5">X</span>
                                    <span>${jogo.oddEmpate}</span>
                                </button>
                                <button class="flex-1 py-1.5 text-[10px] bg-slate-800 hover:bg-laranja/20 hover:text-laranja border border-slate-700 rounded-lg transition uppercase font-black text-gray-300 flex flex-col items-center">
                                    <span class="text-[8px] text-gray-500 font-bold mb-0.5">Fora</span>
                                    <span class="text-laranja">${jogo.oddFora}</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-700/50">
                            <a href="https://api.whatsapp.com/send?text=Onde assistir ${jogo.timeCasa} x ${jogo.timeFora}: ${jogo.link}" target="_blank" class="text-gray-500 hover:text-green-500 transition text-sm">
                                <i class="fab fa-whatsapp"></i> Compartilhar
                            </a>
                            <a href="${jogo.link}" class="bg-laranja/10 hover:bg-laranja text-laranja hover:text-white px-4 py-1.5 rounded-full text-xs font-bold transition">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </article>
                `
            }).join("");
        }

        // Funções de Favorito
        function salvarTimeFavorito() {
            const input = document.getElementById('input-time-favorito');
            if (input.value.trim()) {
                localStorage.setItem('time_favorito', input.value.trim());
                checkFavoritoUI();
                renderizarCards('todos');
            }
        }

        function limparTimeFavorito() {
            localStorage.removeItem('time_favorito');
            document.getElementById('input-time-favorito').value = '';
            checkFavoritoUI();
            renderizarCards('todos');
        }

        function checkFavoritoUI() {
            const display = document.getElementById('time-selecionado-display');
            const timeFav = localStorage.getItem('time_favorito');
            if (timeFav && display) {
                display.classList.remove('hidden');
                display.querySelector('span').innerText = `⭐ Time Favorito: ${timeFav}`;
            } else if (display) {
                display.classList.add('hidden');
            }
        }

        // Controle de filtros
        document.addEventListener('DOMContentLoaded', function() {
            checkFavoritoUI();
            const botoes = document.querySelectorAll('.btn-filter');
            botoes.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filtro = this.getAttribute('data-filtro');
                    botoes.forEach(b => {
                        b.classList.remove('active', 'bg-laranja', 'text-white');
                        b.classList.add('bg-slate-800', 'text-gray-200', 'border-slate-600');
                    });
                    this.classList.add('active', 'bg-laranja', 'text-white');
                    this.classList.remove('bg-slate-800', 'text-gray-200');
                    renderizarCards(filtro);
                });
            });

            const menuBtn = document.getElementById('menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            if(menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            renderizarCards('todos');

            // REGISTRO DO PWA (Service Worker)
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(reg => console.log('PWA ativo!', reg))
                        .catch(err => console.log('Erro no PWA:', err));
                });
            }

            // LÓGICA DO BANNER DE INSTALAÇÃO
            let deferredPrompt;
            const banner = document.getElementById('pwa-install-banner');
            const btnInstall = document.getElementById('btn-install-pwa');
            const btnClose = document.getElementById('close-pwa-banner');

            window.addEventListener('beforeinstallprompt', (e) => {
                // Impede que o navegador mostre o prompt padrão
                e.preventDefault();
                // Guarda o evento para disparar depois
                deferredPrompt = e;
                
                // Verifica se o usuário já fechou recentemente
                const isClosed = localStorage.getItem('pwa-banner-closed');
                if (!isClosed) {
                    banner.classList.remove('hidden');
                    setTimeout(() => {
                        banner.classList.remove('translate-y-full');
                    }, 1000);
                }
            });

            btnInstall.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        banner.classList.add('translate-y-full');
                    }
                    deferredPrompt = null;
                }
            });

            btnClose.addEventListener('click', () => {
                banner.classList.add('translate-y-full');
                // Salva que o usuário fechou para não incomodar por 7 dias
                localStorage.setItem('pwa-banner-closed', Date.now());
                setTimeout(() => {
                    banner.classList.add('hidden');
                }, 500);
            });

            // Limpa o bloqueio após 7 dias
            const lastClosed = localStorage.getItem('pwa-banner-closed');
            if (lastClosed && Date.now() - lastClosed > 7 * 24 * 60 * 60 * 1000) {
                localStorage.removeItem('pwa-banner-closed');
            }
        });
    </script>
</body>
</html>

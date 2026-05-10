<?php get_header(); ?>

<main class="container mx-auto px-4 py-16">
    <div class="max-w-4xl mx-auto">
        <header class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-black text-white uppercase tracking-tighter">Fale <span class="text-laranja">Conosco</span></h1>
            <p class="text-gray-400 mt-4">Dúvidas, sugestões ou propostas comerciais? Estamos prontos para te ouvir.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Informações -->
            <div class="bg-slate-800 rounded-3xl p-8 border border-slate-700 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-6">Canais Oficiais</h3>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-laranja/20 rounded-2xl flex items-center justify-center text-laranja shrink-0">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">E-mail Principal</p>
                            <p class="text-white font-bold text-lg">contato@assistirjogos.com.br</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400 shrink-0">
                            <i class="fab fa-telegram-plane text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">Telegram / Parcerias</p>
                            <p class="text-white font-bold text-lg">@assistirjogos_oficial</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 pt-8 border-t border-slate-700">
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Nossa equipe de suporte trabalha de <strong>Segunda a Sexta, das 09h às 18h</strong>. Respondemos a todas as mensagens em até 48 horas úteis.
                    </p>
                </div>
            </div>

            <!-- Card de Transparência -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-8 border border-slate-700 shadow-2xl flex flex-col justify-center">
                <i class="fas fa-handshake text-laranja text-5xl mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-4">Seja um Parceiro</h3>
                <p class="text-gray-400 leading-relaxed mb-6">
                    Se você é proprietário de um canal de transmissão, streaming ou bar esportivo, temos soluções personalizadas para destacar sua marca em nosso Match Center.
                </p>
                <a href="mailto:contato@assistirjogos.com.br" class="inline-block text-center bg-laranja hover:bg-orange-600 text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-laranja/20">
                    Enviar Proposta por E-mail
                </a>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>

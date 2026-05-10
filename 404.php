<?php get_header(); ?>

<main class="min-h-[70vh] flex items-center justify-center container mx-auto px-4 py-20">
    <div class="text-center space-y-8 max-w-2xl">
        <div class="relative inline-block">
            <h1 class="text-[120px] md:text-[180px] font-black text-slate-800 leading-none select-none">404</h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-futbol text-6xl md:text-8xl text-laranja animate-bounce"></i>
            </div>
        </div>
        
        <div class="space-y-4">
            <h2 class="text-3xl md:text-5xl font-bold text-white uppercase tracking-tighter">Ops! Impedimento marcado.</h2>
            <p class="text-gray-400 text-lg">Parece que essa página saiu pela linha de fundo ou o link expirou. Não se preocupe, a bola ainda está rolando!</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-8">
            <a href="<?php echo home_url(); ?>" class="bg-laranja hover:bg-orange-600 text-white font-bold px-8 py-4 rounded-2xl shadow-xl shadow-laranja/20 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-home"></i> Voltar para o Início
            </a>
            <a href="<?php echo home_url('#jogos-hoje'); ?>" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-8 py-4 rounded-2xl border border-slate-700 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-play-circle"></i> Ver Jogos de Hoje
            </a>
        </div>

        <div class="pt-12">
            <p class="text-gray-500 text-sm uppercase font-bold tracking-widest mb-4">Ou pesquise pelo seu time:</p>
            <form role="search" method="get" class="relative max-w-md mx-auto" action="<?php echo home_url('/'); ?>">
                <input type="search" name="s" class="w-full bg-slate-900 border border-slate-700 text-white px-6 py-4 rounded-2xl focus:outline-none focus:border-laranja transition-colors" placeholder="Ex: Flamengo, Palmeiras...">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-laranja">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</main>

<?php get_footer(); ?>

<?php get_header(); ?>

<main class="container mx-auto px-4 py-16">
    <article class="max-w-4xl mx-auto bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 overflow-hidden">
        <header class="relative h-64 flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-laranja to-orange-600 opacity-90 z-10"></div>
            <div class="relative z-20 text-center px-6">
                <h1 class="text-4xl md:text-6xl font-black text-white uppercase tracking-tighter">Sobre o <span class="text-slate-900">Assistir Jogos</span></h1>
                <p class="text-white/80 font-bold mt-2 uppercase text-xs tracking-widest">O seu guia definitivo de programação esportiva</p>
            </div>
        </header>
        
        <div class="p-8 md:p-12 prose prose-invert prose-orange max-w-none text-gray-300 leading-relaxed">
            <h2 class='text-2xl font-bold mb-4'>Quem Somos</h2>
            <p class='mb-6 text-lg'>O <strong>Assistir Jogos</strong> nasceu da paixão pelo esporte e da necessidade de organizar o caos das transmissões esportivas modernas. Em um mundo com dezenas de streamings e canais, nossa missão é simples: fazer você não perder nenhum segundo da bola rolando.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-10 not-prose">
                <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-700">
                    <i class="fas fa-bullseye text-laranja text-2xl mb-4"></i>
                    <h3 class="text-white font-bold mb-2">Nossa Missão</h3>
                    <p class="text-sm text-gray-400">Ser o guia mais rápido, limpo e confiável para o torcedor brasileiro encontrar sua transmissão oficial.</p>
                </div>
                <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-700">
                    <i class="fas fa-shield-alt text-laranja text-2xl mb-4"></i>
                    <h3 class="text-white font-bold mb-2">Legitimidade</h3>
                    <p class="text-sm text-gray-400">Não hospedamos streams. Trabalhamos apenas com informações oficiais e links para canais licenciados.</p>
                </div>
            </div>

            <h3 class='text-xl font-bold mb-3'>Nossa Curadoria</h3>
            <p class='mb-6'>Nossa equipe realiza uma verificação diária em guias de programação oficiais, sites de federações e comunicados de imprensa das emissoras. Isso garante que o horário que você vê aqui seja o mais preciso possível.</p>
            
            <p class='mb-6'>Acreditamos no esporte como ferramenta de união e entretenimento, e nossa plataforma foi construída para que a tecnologia ajude você a chegar mais rápido ao campo.</p>
        </div>
    </article>
</main>

<?php get_footer(); ?>

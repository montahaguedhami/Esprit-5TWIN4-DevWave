<?php
    use App\Data\PlaceholderData;
    $stats = PlaceholderData::stats();
?>

<?php $__env->startSection('public-content'); ?>
<div class="landing-shell min-h-screen bg-white text-slate-800 font-sans selection:bg-cyan-500 selection:text-white">

    
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 transition-all"
            style="height: var(--navbar-height);">
        <div class="fo-container h-full flex items-center justify-between gap-8">

            
            <a href="<?php echo e(route('landing')); ?>" class="flex items-center gap-3.5 group shrink-0">
                <div class="navbar-logo rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-transform">
                    <i data-lucide="droplet" style="width:52%; height:52%;" class="text-white fill-white"></i>
                </div>
                <span class="font-display font-extrabold tracking-tight" style="font-size: clamp(1.3rem, 1.7vw, 1.75rem);">
                    <span class="text-cyan-500">Aqua</span><span class="text-[#0c2a4a]">Secure</span>
                </span>
            </a>

            
            <nav class="hidden md:flex items-center gap-8 font-semibold text-slate-600" style="font-size: var(--font-size-nav);">
                <a href="#accueil" class="relative text-cyan-600 font-bold py-1 after:content-[''] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-cyan-500 after:rounded-full">Accueil</a>
                <a href="#about"    class="hover:text-cyan-600 transition-colors py-1">À propos</a>
                <a href="#features" class="hover:text-cyan-600 transition-colors py-1">Fonctionnalités</a>
                <a href="#advantages" class="hover:text-cyan-600 transition-colors py-1">Avantages</a>
                <a href="#contact"  class="hover:text-cyan-600 transition-colors py-1">Contact</a>
            </nav>

            
            <div class="flex items-center gap-4">
                <a href="<?php echo e(route('auth.login')); ?>"
                   class="btn-primary-md bg-gradient-to-r from-[#0c2a4a] to-[#0e3460] hover:from-cyan-600 hover:to-blue-700 text-white shadow-lg shadow-slate-900/15 hover:shadow-xl hover:-translate-y-0.5"
                   style="border-radius: 999px;">
                    <i data-lucide="user" style="width:var(--icon-sm); height:var(--icon-sm);" class="text-cyan-300"></i>
                    <span>Se connecter</span>
                </a>
                <button onclick="toggleMobileNav()" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors" aria-label="Menu">
                    <i data-lucide="menu" style="width:var(--icon-md); height:var(--icon-md);"></i>
                </button>
            </div>
        </div>

        
        <div id="mobile-nav" class="hidden md:hidden bg-white border-t border-slate-100 px-6 pb-4 pt-3 space-y-1">
            <a href="#accueil"    class="block px-4 py-2.5 rounded-xl text-cyan-600 font-bold bg-cyan-50" style="font-size: var(--font-size-nav);">Accueil</a>
            <a href="#about"      class="block px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 font-medium" style="font-size: var(--font-size-nav);">À propos</a>
            <a href="#features"   class="block px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 font-medium" style="font-size: var(--font-size-nav);">Fonctionnalités</a>
            <a href="#advantages" class="block px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 font-medium" style="font-size: var(--font-size-nav);">Avantages</a>
            <a href="#contact"    class="block px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 font-medium" style="font-size: var(--font-size-nav);">Contact</a>
        </div>
    </header>


    
    <section id="accueil" class="relative flex items-center overflow-hidden bg-gradient-to-b from-cyan-50/40 to-white"
             style="min-height: clamp(640px, 85vh, 900px); padding-top: var(--navbar-height);">

        <div class="absolute inset-0 z-0">
            <img src="./assets/water-background.jpg" alt="AquaSecure Infrastructure" class="w-full h-full object-cover object-center opacity-70">
            <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/85 to-white/20"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-white/30 via-transparent to-white"></div>
        </div>

        <div class="fo-container relative z-10 w-full" style="padding-top: clamp(2rem,4vw,4rem); padding-bottom: clamp(2rem,4vw,4rem);">
            <div class="grid lg:grid-cols-2 items-center" style="gap: clamp(2.5rem, 5vw, 6rem);">

                
                <div style="display:flex; flex-direction:column; gap: clamp(1.25rem, 2vw, 2rem);">

                    <div class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full bg-cyan-100/90 backdrop-blur-md border border-cyan-200 text-cyan-800 font-semibold self-start shadow-sm"
                         style="font-size: var(--font-size-sm);">
                        <span>💧</span>
                        <span>Une eau plus sûre, un avenir plus durable</span>
                    </div>

                    <div>
                        <span class="block font-display font-bold text-cyan-600 mb-3" style="font-size: clamp(1.1rem,1.5vw,1.4rem);">AquaSecure</span>
                        <h1 class="hero-title text-slate-900 font-display">
                            Centralisez le suivi de<br class="hidden xl:block">
                            <span class="text-cyan-500">votre réseau d'eau</span>
                        </h1>
                    </div>

                    <p class="hero-subtitle text-slate-600 font-medium" style="max-width: 600px;">
                        AquaSecure centralise les signalements et les informations sur le réseau d'eau pour faciliter le suivi des incidents et la coordination des interventions.
                    </p>

                    <div class="flex flex-wrap items-center" style="gap: clamp(0.75rem,1.2vw,1.25rem);">
                        <a href="<?php echo e(route('auth.login')); ?>"
                           class="btn-primary-lg bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 hover:-translate-y-0.5 group">
                            <span>Découvrir la solution</span>
                            <i data-lucide="arrow-right" style="width:var(--icon-sm);height:var(--icon-sm);" class="group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#features"
                           class="btn-primary-lg bg-white/80 hover:bg-white text-cyan-700 font-bold border-2 border-cyan-400/40 shadow-sm hover:-translate-y-0.5">
                            En savoir plus
                        </a>
                    </div>

                    <div class="flex items-center" style="gap: clamp(1.5rem,3vw,3rem); padding-top: 0.5rem;">
                        <div>
                            <div class="font-display font-black text-slate-900" style="font-size: clamp(1.5rem,2.2vw,2.2rem);"><?php echo e($stats['totalZones']); ?></div>
                            <div class="text-slate-500 font-medium" style="font-size: var(--font-size-xs);">Zones cartographiées</div>
                        </div>
                        <div class="w-px h-10 bg-slate-200"></div>
                        <div>
                            <div class="font-display font-black text-slate-900" style="font-size: clamp(1.5rem,2.2vw,2.2rem);"><?php echo e($stats['activeIncidents']); ?></div>
                            <div class="text-slate-500 font-medium" style="font-size: var(--font-size-xs);">Incidents à suivre</div>
                        </div>
                        <div class="w-px h-10 bg-slate-200"></div>
                        <div>
                            <div class="font-display font-black text-slate-900" style="font-size: clamp(1.5rem,2.2vw,2.2rem);">3</div>
                            <div class="text-slate-500 font-medium" style="font-size: var(--font-size-xs);">Domaines de suivi</div>
                        </div>
                    </div>
                </div>

                
                <div class="hidden lg:flex flex-col items-stretch" style="gap: clamp(1rem,1.5vw,1.5rem);">

                    <div class="hero-float-card bg-white/97 backdrop-blur-xl border border-slate-100 shadow-2xl shadow-cyan-900/10 text-slate-900 hover:-translate-y-1 transition-transform">
                        <div class="flex items-start" style="gap: clamp(1rem,1.5vw,1.5rem);">
                            <div class="flex items-center justify-center bg-cyan-100/90 text-cyan-600 shrink-0"
                                 style="width:var(--icon-wrap-md);height:var(--icon-wrap-md);border-radius:1rem;">
                                <i data-lucide="droplet" style="width:var(--icon-md);height:var(--icon-md);"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-bold text-slate-900" style="font-size:var(--font-size-h4);">Suivi des fuites signalées</h3>
                                    <i data-lucide="chevron-right" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-slate-400"></i>
                                </div>
                                <p class="text-slate-500 leading-relaxed" style="font-size:var(--font-size-sm);">Centralisez les signalements et suivez leur prise en charge.</p>
                                <div class="mt-3 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-bold" style="font-size:var(--font-size-xs);">
                                    Signalement enregistré
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hero-float-card bg-gradient-to-br from-white to-cyan-50/80 border border-cyan-200/80 shadow-xl text-slate-900 flex items-center justify-between">
                        <div class="flex items-center" style="gap: clamp(0.75rem,1.2vw,1.2rem);">
                            <div class="relative shrink-0" style="width:var(--icon-wrap-md);height:var(--icon-wrap-md);">
                                <div class="w-full h-full rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30">
                                    <i data-lucide="map" style="width:var(--icon-md);height:var(--icon-md);"></i>
                                </div>
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-400 border-2 border-white"></span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900" style="font-size:var(--font-size-sm);">Plateforme AquaSecure</div>
                                <div class="text-slate-500 mt-0.5" style="font-size:var(--font-size-xs);">Suivi &amp; Analyse du Réseau</div>
                            </div>
                        </div>
                        <div class="shrink-0 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center" style="width:var(--icon-wrap-sm);height:var(--icon-wrap-sm);">
                            <i data-lucide="droplet" style="width:var(--icon-sm);height:var(--icon-sm);"></i>
                        </div>
                    </div>

                    <div class="hero-float-card bg-gradient-to-r from-[#0c2a4a] to-[#0e3460] text-white shadow-xl">
                        <div class="flex items-center" style="gap: clamp(0.75rem,1.2vw,1.2rem);">
                            <div class="shrink-0 bg-white/10 flex items-center justify-center rounded-xl" style="width:var(--icon-wrap-sm);height:var(--icon-wrap-sm);">
                                <i data-lucide="shield-check" style="width:var(--icon-md);height:var(--icon-md);" class="text-cyan-300"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold" style="font-size:var(--font-size-sm);">Données centralisées</div>
                                <div class="text-cyan-300/80 mt-0.5" style="font-size:var(--font-size-xs);">Conduites · qualité · incidents</div>
                            </div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse shrink-0"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 z-10 overflow-hidden leading-none pointer-events-none">
            <svg class="relative block w-full text-white" style="height: clamp(40px,4vw,80px);" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0 C150,90 350,-40 500,50 C650,140 900,10 1200,40 L1200,120 L0,120 Z" fill="currentColor"></path>
            </svg>
        </div>
    </section>


    
    <section id="features" class="bg-white border-b border-slate-100" style="padding: var(--section-py) 0;">
        <div class="fo-container">

            <div class="text-center" style="max-width:800px;margin:0 auto clamp(2.5rem,4vw,4rem);">
                <span class="section-label text-cyan-600 block mb-4">Outils de gestion</span>
                <h2 class="section-h2 text-slate-900 font-display mb-5">Des outils pour mieux gérer le réseau d'eau</h2>
                <p class="section-body text-slate-500">Consultez les informations du réseau, gérez les signalements et préparez les interventions depuis un même espace.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4" style="gap: var(--card-gap);">
                <?php
                $features = [
                    ['icon'=>'droplet',     'bg'=>'bg-cyan-50',   'text'=>'text-cyan-500',   'title'=>'Suivi du réseau',            'desc'=>'Consultez les informations sur les conduites et les zones depuis un tableau de bord centralisé.'],
                    ['icon'=>'clipboard-list','bg'=>'bg-blue-50',  'text'=>'text-blue-500',   'title'=>'Gestion des signalements',   'desc'=>'Enregistrez les problèmes signalés et suivez leur prise en charge par les équipes.'],
                    ['icon'=>'leaf',        'bg'=>'bg-teal-50',   'text'=>'text-teal-500',   'title'=>"Préservation de l'eau",      'desc'=>"Identifiez les zones prioritaires et planifiez des actions pour limiter les pertes."],
                    ['icon'=>'bar-chart-3', 'bg'=>'bg-indigo-50', 'text'=>'text-indigo-500', 'title'=>'Rapports et suivi',          'desc'=>'Consultez des rapports détaillés et suivez les actions prévues par vos équipes.'],
                ];
                ?>
                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="feature-card bg-white border border-slate-100 text-center shadow-lg shadow-slate-100 hover:shadow-xl hover:border-cyan-200 hover:-translate-y-1 transition-all group">
                    <div class="feature-icon-wrap <?php echo e($feat['bg']); ?> <?php echo e($feat['text']); ?> mx-auto mb-6">
                        <i data-lucide="<?php echo e($feat['icon']); ?>" style="width:var(--icon-lg);height:var(--icon-lg);"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 mb-3" style="font-size:var(--font-size-h4);"><?php echo e($feat['title']); ?></h3>
                    <p class="text-slate-500 leading-relaxed" style="font-size:var(--font-size-sm);"><?php echo e($feat['desc']); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>


    
    <section id="about" class="border-b border-slate-100 bg-gradient-to-b from-white to-slate-50/70" style="padding: var(--section-py) 0;">
        <div class="fo-container">
            <div class="grid lg:grid-cols-2 items-center" style="gap: clamp(3rem,5vw,6rem);">

                <div style="display:flex;flex-direction:column;gap:clamp(1rem,1.8vw,1.75rem);">
                    <span class="section-label text-cyan-600">À propos d'AquaSecure</span>
                    <h2 class="section-h2 text-slate-900 font-display leading-tight">Une plateforme pour suivre et gérer le réseau d'eau</h2>
                    <p class="section-body text-slate-600 leading-relaxed">AquaSecure est une plateforme SaaS qui centralise les informations sur le réseau d'eau pour faciliter son suivi, la gestion des incidents et la consultation de la qualité de l'eau.</p>
                    <p class="text-slate-500 leading-relaxed" style="font-size:var(--font-size-sm);">Les équipes retrouvent au même endroit les informations sur les zones, les incidents et la qualité de l'eau pour établir leurs priorités et planifier leurs interventions.</p>
                    <div class="grid grid-cols-2" style="gap:var(--spacing-md);">
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm" style="padding:var(--card-padding);">
                            <div class="font-display font-black text-cyan-500 stat-number"><?php echo e($stats['totalZones']); ?></div>
                            <div class="font-semibold text-slate-600 mt-1" style="font-size:var(--font-size-sm);">Zones cartographiées</div>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm" style="padding:var(--card-padding);">
                            <div class="font-display font-black text-blue-600 stat-number"><?php echo e($stats['activeIncidents']); ?></div>
                            <div class="font-semibold text-slate-600 mt-1" style="font-size:var(--font-size-sm);">Incidents à suivre</div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-cyan-500 to-blue-700 rounded-3xl text-white shadow-2xl" style="padding:var(--card-padding-lg);">
                    <div class="rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-6" style="width:var(--icon-wrap-lg);height:var(--icon-wrap-lg);">
                        <i data-lucide="cpu" style="width:var(--icon-md);height:var(--icon-md);" class="text-white"></i>
                    </div>
                    <h3 class="font-bold mb-4" style="font-size:var(--font-size-h3);">Suivi du réseau et des interventions</h3>
                    <p class="text-cyan-100 leading-relaxed" style="font-size:var(--font-size-body);">La plateforme rassemble les informations sur les conduites, les signalements et les analyses de qualité afin d'aider les équipes à repérer les priorités et organiser les interventions.</p>
                    <div class="mt-8 pt-6 border-t border-white/10 flex items-center justify-between text-cyan-200" style="font-size:var(--font-size-xs);">
                        <span>Informations centralisées</span>
                        <span>Suivi du réseau &amp; des interventions</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    
    <section id="advantages" class="bg-white border-b border-slate-100" style="padding: var(--section-py) 0;">
        <div class="fo-container">
            <div class="text-center" style="max-width:900px;margin:0 auto clamp(3rem,5vw,5rem);">
                <span class="section-label text-cyan-600 block mb-4">Pourquoi Choisir AquaSecure</span>
                <h2 class="section-h2 text-slate-900 font-display mb-5">Des avantages concrets pour vos installations</h2>
                <p class="section-body text-slate-500">Des fonctions pratiques pour organiser le suivi du réseau, des incidents et des interventions.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3" style="gap:var(--card-gap);">
                <?php
                $advantages = [
                    ['icon'=>'bell-ring',  'bg'=>'bg-cyan-100',    'text'=>'text-cyan-600',    'title'=>'Suivi des signalements',  'desc'=>"Consultez les problèmes remontés et l'avancement de leur prise en charge."],
                    ['icon'=>'piggy-bank', 'bg'=>'bg-blue-100',    'text'=>'text-blue-600',    'title'=>'Économies Financières',   'desc'=>"Réduction directe des surcoûts d'eau non comptabilisée et optimisation des interventions de maintenance."],
                    ['icon'=>'globe',      'bg'=>'bg-emerald-100', 'text'=>'text-emerald-600', 'title'=>'Empreinte Écologique',    'desc'=>'Préservation active des nappes phréatiques et alignement avec vos objectifs RSE et environnementaux.'],
                ];
                ?>
                <?php $__currentLoopData = $advantages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gradient-to-b from-slate-50/80 to-white border border-slate-100 rounded-3xl hover:border-cyan-200 transition-all shadow-sm" style="padding:var(--card-padding-lg);display:flex;flex-direction:column;gap:var(--spacing-md);">
                    <div class="<?php echo e($adv['bg']); ?> <?php echo e($adv['text']); ?> rounded-2xl flex items-center justify-center" style="width:var(--icon-wrap-md);height:var(--icon-wrap-md);">
                        <i data-lucide="<?php echo e($adv['icon']); ?>" style="width:var(--icon-md);height:var(--icon-md);"></i>
                    </div>
                    <h3 class="font-bold text-slate-900" style="font-size:var(--font-size-h4);"><?php echo e($adv['title']); ?></h3>
                    <p class="text-slate-500 leading-relaxed" style="font-size:var(--font-size-sm);"><?php echo e($adv['desc']); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>


    
    <section id="contact" class="bg-slate-50/50" style="padding: var(--section-py) 0;">
        <div class="fo-container">
            <div class="grid lg:grid-cols-5 items-start" style="gap:clamp(3rem,5vw,6rem);">

                <div class="lg:col-span-2" style="display:flex;flex-direction:column;gap:clamp(1rem,1.8vw,2rem);">
                    <span class="section-label text-cyan-600">Contact &amp; Démonstration</span>
                    <h2 class="section-h2 text-slate-900 font-display leading-tight">Prêt à sécuriser vos réseaux d'eau&nbsp;?</h2>
                    <p class="text-slate-600 leading-relaxed" style="font-size:var(--font-size-body);">Demandez une démonstration personnalisée ou contactez nos experts.</p>
                    <div style="display:flex;flex-direction:column;gap:var(--spacing-md);">
                        <?php $__currentLoopData = [['mail','Email','contact@aquasecure.io'],['phone','Téléphone','+33 (0)1 89 00 00 00']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic,$label,$val]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-4">
                            <div class="bg-white border border-slate-200 text-cyan-600 flex items-center justify-center shadow-sm rounded-2xl shrink-0" style="width:var(--icon-wrap-sm);height:var(--icon-wrap-sm);">
                                <i data-lucide="<?php echo e($ic); ?>" style="width:var(--icon-sm);height:var(--icon-sm);"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-slate-400" style="font-size:var(--font-size-xs);"><?php echo e($label); ?></div>
                                <div class="font-bold text-slate-800" style="font-size:var(--font-size-sm);"><?php echo e($val); ?></div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <form onsubmit="event.preventDefault(); alert('Merci ! Notre équipe vous recontactera sous 24h.');" class="bg-white border border-slate-100 rounded-3xl shadow-xl" style="padding:var(--card-padding-lg);">
                        <div class="grid sm:grid-cols-2" style="gap:var(--spacing-md);margin-bottom:var(--spacing-md);">
                            <div>
                                <label class="font-bold text-slate-700 block mb-2" style="font-size:var(--font-size-xs);">Nom complet</label>
                                <input type="text" required placeholder="Jean Dupont" class="w-full rounded-2xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 outline-none transition-all" style="padding:var(--spacing-sm) var(--spacing-md);font-size:var(--font-size-sm);">
                            </div>
                            <div>
                                <label class="font-bold text-slate-700 block mb-2" style="font-size:var(--font-size-xs);">Email professionnel</label>
                                <input type="email" required placeholder="j.dupont@entreprise.com" class="w-full rounded-2xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 outline-none transition-all" style="padding:var(--spacing-sm) var(--spacing-md);font-size:var(--font-size-sm);">
                            </div>
                        </div>
                        <div style="margin-bottom:var(--spacing-md);">
                            <label class="font-bold text-slate-700 block mb-2" style="font-size:var(--font-size-xs);">Organisation / Entreprise</label>
                            <input type="text" placeholder="Nom de votre structure" class="w-full rounded-2xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 outline-none transition-all" style="padding:var(--spacing-sm) var(--spacing-md);font-size:var(--font-size-sm);">
                        </div>
                        <div style="margin-bottom:var(--spacing-lg);">
                            <label class="font-bold text-slate-700 block mb-2" style="font-size:var(--font-size-xs);">Message</label>
                            <textarea rows="4" required placeholder="Décrivez votre besoin ou projet..." class="w-full rounded-2xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 outline-none transition-all resize-none" style="padding:var(--spacing-sm) var(--spacing-md);font-size:var(--font-size-sm);"></textarea>
                        </div>
                        <button type="submit" class="btn-primary-lg w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl" style="border-radius:9999px;">
                            Envoyer ma demande de démonstration →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    
    <footer class="bg-[#0c2a4a] text-slate-300" style="padding: clamp(2rem,4vw,4rem) 0;">
        <div class="fo-container flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center" style="width:var(--icon-wrap-sm);height:var(--icon-wrap-sm);">
                    <i data-lucide="droplet" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-white"></i>
                </div>
                <span class="font-display font-bold text-white" style="font-size:var(--font-size-h4);">AquaSecure</span>
            </div>
            <p style="font-size:var(--font-size-xs);">© <?php echo e(date('Y')); ?> AquaSecure — Plateforme de gestion du réseau d'eau. Tous droits réservés.</p>
            <a href="#accueil" class="hover:text-cyan-400 transition-colors" style="font-size:var(--font-size-xs);">Haut de page ↑</a>
        </div>
    </footer>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleMobileNav() {
        const nav = document.getElementById('mobile-nav');
        nav.classList.toggle('hidden');
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/landing.blade.php ENDPATH**/ ?>
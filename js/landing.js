(function () {
    // Marquee tilt + announce
    const shell = document.querySelector('.marquee-shell');
    const items = document.getElementById('marqueeItems');
    const announcer = document.getElementById('srAnnouncer');
    if (items && shell) {
        shell.addEventListener('mousemove', function (e) {
            const rect = shell.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            const rotY = x * 6;
            const rotX = y * -4;
            shell.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
        });
        shell.addEventListener('mouseleave', () => shell.style.transform = '');
        try {
            const first = items.querySelector('.marquee-item');
            if (first && announcer) announcer.textContent = first.textContent.trim();
        } catch (e) { console.error(e); }
    }

    // Staff modal binding
    (function () {
        const modalEl = document.getElementById('staffDetailModal');
        if (!modalEl) return;
        const bsModal = new bootstrap.Modal(modalEl);
        const title = document.getElementById('staffDetailTitle');
        const photo = document.getElementById('staffDetailPhoto');
        const designation = document.getElementById('staffDetailDesignation');
        const qualification = document.getElementById('staffDetailQualification');
        const bio = document.getElementById('staffDetailBio');

        document.querySelectorAll('.staff-card').forEach(card => {
            card.addEventListener('click', () => {
                const name = card.getAttribute('data-staff-name') || '';
                const photoSrc = card.getAttribute('data-staff-photo') || '';
                const desig = card.getAttribute('data-staff-designation') || '';
                const qual = card.getAttribute('data-staff-qualification') || '';
                const bios = card.getAttribute('data-staff-bio') || '';
                title.textContent = name;
                if (photoSrc) { photo.src = photoSrc; photo.style.display = 'block'; } else { photo.style.display = 'none'; }
                designation.textContent = desig;
                qualification.textContent = qual;
                bio.innerHTML = bios ? bios.replace(/\n/g, '<br>') : '';
                bsModal.show();
            });
        });
    })();

    // Mouse-driven parallax for hero
    (function () {
        const scene = document.getElementById('scene');
        if (!scene) return;
        const maxTilt = 12;
        let w = scene.clientWidth, h = scene.clientHeight;
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function handleMove(e) {
            const rect = scene.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            const rotY = x * maxTilt * -1;
            const rotX = y * maxTilt;
            scene.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
            if (!prefersReduced) {
                const layers = scene.querySelectorAll('.layer');
                layers.forEach((L, i) => {
                    const depth = (i + 1) * 12;
                    const tx = x * depth;
                    const ty = y * depth;
                    L.style.transform = `translate3d(${tx}px, ${ty}px, ${-depth * 5}px)`;
                });
            }
        }

        function handleOut() {
            scene.style.transform = '';
            scene.querySelectorAll('.layer').forEach((L) => L.style.transform = '');
        }

        if (!prefersReduced) {
            scene.addEventListener('mousemove', handleMove);
            scene.addEventListener('mouseleave', handleOut);
        }
        window.addEventListener('resize', () => { w = scene.clientWidth; h = scene.clientHeight; });
    })();

    // Landing gallery preview behavior and lightbox
    (function(){
        const zone = document.getElementById('landingGallery');
        if (!zone) return;
        const cards = Array.from(zone.querySelectorAll('.lg-card'));
        const lb = document.getElementById('lgLightbox');
        const lbImg = document.getElementById('lg-img');
        const lbTitle = document.getElementById('lg-title');
        const lbDesc = document.getElementById('lg-desc');

        function bind(){
            cards.forEach((card, idx)=>{
                if (card.dataset.bound) return; card.dataset.bound=1;
                card.addEventListener('click', ()=>open(card));
                card.addEventListener('keydown', (e)=>{ if (e.key==='Enter') open(card); });
            });
        }
        function open(card){
            const src = card.getAttribute('data-src');
            lbImg.src = src; lbImg.alt = card.querySelector('.lg-meta strong')?.textContent || '';
            lbTitle.textContent = card.querySelector('.lg-meta strong')?.textContent || '';
            lbDesc.textContent = card.querySelector('.lg-meta .small')?.textContent || '';
            lb.style.display = 'flex'; lb.setAttribute('aria-hidden','false');
        }
        function close(){ lb.style.display='none'; lb.setAttribute('aria-hidden','true'); lbImg.src=''; }
        document.getElementById('lg-close')?.addEventListener('click', close);
        lb.addEventListener('click', (e)=>{ if (e.target===lb) close(); });
        document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') close(); });

        // Mouse tilt (respect reduced-motion). serverForceReduced is provided by landing.php inline script
        const serverForceReduced = window.serverForceReduced === true || window.serverForceReduced === 'true';
        const prefersReduced = (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) || serverForceReduced;
        if (!prefersReduced) {
            zone.addEventListener('mousemove', (e)=>{
                const rect = zone.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                cards.forEach((card, i)=>{
                    const base = parseInt(card.getAttribute('data-effect') || 0);
                    const strength = base || 6;
                    const rx = (-y * strength).toFixed(2)+ 'deg';
                    const ry = (x * strength).toFixed(2)+ 'deg';
                    card.style.setProperty('--rx', rx);
                    card.style.setProperty('--ry', ry);
                });
            });
            zone.addEventListener('mouseleave', ()=>{ cards.forEach(c=>{ c.style.setProperty('--rx','0'); c.style.setProperty('--ry','0'); }); });
        }
        bind();
    })();
})();

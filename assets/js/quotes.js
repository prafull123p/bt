document.addEventListener('DOMContentLoaded', function(){
  const carouselEl = document.getElementById('quotesCarousel');
  if (!carouselEl) return;
  const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);

  function handleKey(e){
    if (e.key === 'ArrowLeft') { carousel.prev(); }
    else if (e.key === 'ArrowRight') { carousel.next(); }
  }

  carouselEl.addEventListener('mouseenter', ()=> document.addEventListener('keydown', handleKey));
  carouselEl.addEventListener('mouseleave', ()=> document.removeEventListener('keydown', handleKey));

  // Load more functionality: fetch next page and append slides lazily
  const loadBtn = document.getElementById('loadMoreQuotes');
  if (!loadBtn) return;

  async function loadPage(page, perPage){
    const url = `api/quotes.php?page=${page}&per_page=${perPage}`;
    const res = await fetch(url, {cache: 'no-store'});
    if (!res.ok) throw new Error('Network error');
    return await res.json();
  }

  loadBtn.addEventListener('click', async function(){
    let nextPage = parseInt(this.dataset.nextPage || this.getAttribute('data-next-page') || '2', 10);
    const perPage = parseInt(this.dataset.perPage || this.getAttribute('data-per-page') || '6', 10);
    this.disabled = true; this.textContent = 'Loading...';
    try {
      const data = await loadPage(nextPage, perPage);
      if (Array.isArray(data.quotes) && data.quotes.length) {
        const inner = carouselEl.querySelector('.carousel-inner');
        data.quotes.forEach(q => {
          const item = document.createElement('div');
          item.className = 'carousel-item';
          const safeQuote = (q.quote||'').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
          const safeAuthor = (q.author||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
          item.innerHTML = `\n            <div class="quote-3d-wrapper d-flex justify-content-center">\n              <div class="quote-scene" aria-live="polite">\n                <div class="quote-card">\n                  <div class="quote-inner">\n                    <div class="quote-text">“${safeQuote}”</div>\n                    <div class="quote-author">— ${safeAuthor}</div>\n                  </div>\n                </div>\n              </div>\n            </div>`;
          inner.appendChild(item);
        });
        // update next page and state
        const newNext = data.next_page || (nextPage + 1);
        this.dataset.nextPage = newNext;
        this.setAttribute('data-next-page', newNext);
        if (!data.has_more) {
          this.disabled = true; this.textContent = 'No more quotes';
        } else {
          this.disabled = false; this.textContent = 'Load more quotes';
        }
      } else {
        this.disabled = true; this.textContent = 'No more quotes';
      }
    } catch (err) {
      console.error(err);
      this.disabled = false; this.textContent = 'Load more quotes';
    }
  });

  // 3D tilt on active slide
  function applyTilt(target, e){
    const rect = target.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    const rotY = x * 8; // degrees
    const rotX = y * -8;
    const scene = target.querySelector('.quote-scene');
    if (scene) {
      scene.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
      const card = scene.querySelector('.quote-card');
      if (card) card.style.transform = `translateZ(60px) rotateX(${rotX*0.4}deg) rotateY(${rotY*0.4}deg)`;
    }
  }

  function resetTilt(target){
    const scene = target.querySelector('.quote-scene');
    if (scene) {
      scene.style.transform = '';
      const card = scene.querySelector('.quote-card');
      if (card) card.style.transform = '';
    }
  }

  // attach mousemove listeners to active slide container; update when slide changes
  function attachTiltHandlers(){
    const items = carouselEl.querySelectorAll('.carousel-item');
    items.forEach(item=>{
      item.removeEventListener('mousemove', item._tiltHandler);
      item.removeEventListener('mouseleave', item._leaveHandler);
      // only apply handlers to visible item
      const tiltHandler = (e)=> applyTilt(item, e);
      const leaveHandler = ()=> resetTilt(item);
      item._tiltHandler = tiltHandler;
      item._leaveHandler = leaveHandler;
      item.addEventListener('mousemove', tiltHandler);
      item.addEventListener('mouseleave', leaveHandler);
    });
  }

  // initial attach
  attachTiltHandlers();
  // Re-attach when carousel slides (so newly added items get handlers)
  carouselEl.addEventListener('slid.bs.carousel', function(){ attachTiltHandlers(); });
  // Also call after load more (when items appended)
  loadBtn.addEventListener('click', function(){ setTimeout(attachTiltHandlers, 400); });
});

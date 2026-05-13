(function(){
  'use strict';
  var KEY='smk_theme', html=document.documentElement;
  
  function getTheme(){
    var s; try{s=localStorage.getItem(KEY);}catch(e){}
    if(s==='dark'||s==='light') return s;
    return window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';
  }
  
  function apply(t){
    html.setAttribute('data-theme',t);
    try{localStorage.setItem(KEY,t);}catch(e){}
    var btn=document.getElementById('themeToggle');
    if(btn){
      var icon=btn.querySelector('.theme-icon')||btn;
      icon.textContent=t==='dark'?'☀️':'🌙';
    }
  }
  
  // Apply immediately to prevent flash
  apply(getTheme());
  
  document.addEventListener('DOMContentLoaded',function(){
    // Theme toggle
    var btn=document.getElementById('themeToggle');
    if(btn) btn.addEventListener('click',function(){
      apply(html.getAttribute('data-theme')==='dark'?'light':'dark');
    });
    
    // Hamburger sidebar
    var ham=document.getElementById('hamburgerBtn');
    if(ham) ham.addEventListener('click',function(){
      document.body.classList.toggle('sidebar-open');
    });
    
    // Overlay close
    var ov=document.querySelector('.sidebar-overlay');
    if(ov) ov.addEventListener('click',function(){document.body.classList.remove('sidebar-open');});
    
    // Close button
    var cl=document.querySelector('.sidebar-close');
    if(cl) cl.addEventListener('click',function(){document.body.classList.remove('sidebar-open');});
    
    // Active nav
    var file=(location.pathname.split('/').pop()||'index.php').split('?')[0];
    document.querySelectorAll('.nav-link,.sidebar nav a').forEach(function(a){
      var h=(a.getAttribute('href')||'').split('/').pop().split('?')[0];
      if(h&&h===file) a.classList.add('active');
    });
    
    // Table wrap
    document.querySelectorAll('table').forEach(function(t){
      if(t.closest('.table-wrap')) return;
      var w=document.createElement('div');
      w.className='table-wrap';
      t.parentNode.insertBefore(w,t);
      w.appendChild(t);
    });
    
    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(function(el){
      setTimeout(function(){el.style.opacity='0';el.style.maxHeight='0';el.style.padding='0';el.style.margin='0';el.style.overflow='hidden';},5000);
    });
    
    // Resize
    window.addEventListener('resize',function(){
      if(window.innerWidth>768) document.body.classList.remove('sidebar-open');
    });
  });
  
  // Confirm
  document.addEventListener('click',function(e){
    var t=e.target.closest('[data-confirm]');
    if(t&&!confirm(t.getAttribute('data-confirm')||'Yakin?')){e.preventDefault();}
  });
})();

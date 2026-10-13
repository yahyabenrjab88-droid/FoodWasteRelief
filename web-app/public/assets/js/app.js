document.addEventListener('DOMContentLoaded',()=>{
  const toast=document.querySelector('.toast');
  if(toast){setTimeout(()=>toast.remove(),3000);}

  document.querySelectorAll('[data-counter]').forEach(el=>{
    const target=parseFloat(el.getAttribute('data-counter')||'0');
    let cur=0;const step=Math.max(1,target/80);
    const t=setInterval(()=>{cur+=step;if(cur>=target){cur=target;clearInterval(t);}el.textContent=Math.floor(cur).toLocaleString('fr-TN');},18);
  });

  const btn=document.getElementById('mobileToggle');
  const sidebar=document.getElementById('sidebar');
  if(btn&&sidebar){btn.addEventListener('click',()=>sidebar.classList.toggle('open'));}
});

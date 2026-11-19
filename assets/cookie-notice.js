(() => {
'use strict';
const key='lchat-cookie-notice-v1';
function saved(){try{return localStorage.getItem(key)==='accepted';}catch{return false;}}
if(saved())return;
const panel=document.createElement('aside');panel.className='cookie-notice';panel.setAttribute('aria-label','Use cookie');
panel.innerHTML='<strong>Cookie and local storage</strong><p>We use cookie for logging into your account, and local storage for interface settings. <a href="cookies.php">More details</a></p><button type="button">I accept</button><span role="status"></span>';
panel.querySelector('button').addEventListener('click',()=>{try{localStorage.setItem(key,'accepted');}catch{}panel.remove();});
document.body.append(panel);
})();

import{G as i,e as a,p as t}from"./CBMsOuPa.js";const o=i(()=>{const e=a();if(!e.isLoggedIn)return t("/admin/login");if(!e.isAdmin)return t("/")});export{o as default};

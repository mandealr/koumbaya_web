import{c as t,M as b,b as i,e,l as c,t as n,g as f,w as m,h as k,F as C,A as w,o as a,i as P,B as q}from"./vendor-CS2FKTs1.js";import{_ as z}from"./app-B9Qu9-mT.js";const N={class:"min-h-screen bg-white"},V={class:"bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#0077aa] text-white py-20"},K={class:"max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8"},L={class:"text-4xl md:text-6xl font-bold mb-6"},S={key:0,class:"text-xl md:text-2xl text-blue-100 leading-relaxed"},T={class:"py-20"},A={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"},B={class:"bg-white rounded-2xl shadow-lg overflow-hidden"},M={class:"p-8 md:p-12"},D=["innerHTML"],F={key:0,class:"mt-12 text-center"},G={key:0,class:"py-20 bg-gray-50"},I={class:"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"},R={class:"grid grid-cols-1 md:grid-cols-3 gap-6"},j={class:"text-xl font-semibold text-gray-900 mb-2"},E={class:"text-gray-600"},H={__name:"StaticPage",setup(Q){const x=b(),s=t(()=>x.path.replace(/^\//,"")),p=t(()=>({about:"Découvrez notre mission et nos valeurs",terms:"Conditions générales d'utilisation de la plateforme Koumbaya",privacy:"Comment nous protégeons vos données personnelles",legal:"Informations légales et réglementaires",cookies:"Notre politique d'utilisation des cookies"})[s.value]||""),g=t(()=>({about:`
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la page À propos...
        </p>
      </div>
    `,terms:`
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour les conditions d'utilisation...
        </p>
      </div>
    `,privacy:`
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la politique de confidentialité...
        </p>
      </div>
    `,legal:`
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour les mentions légales...
        </p>
      </div>
    `,cookies:`
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la politique de cookies...
        </p>
      </div>
    `})[s.value]||`
    <div class="text-center py-12">
      <p class="text-gray-600 text-lg mb-6">
        Cette page sera bientôt disponible avec le contenu approprié.
      </p>
      <p class="text-gray-500">
        Veuillez fournir le contenu spécifique pour cette page.
      </p>
    </div>
  `),v=t(()=>({about:"À propos de Koumbaya",affiliates:"Programme d'Affiliation","earn-gombos":"Gagnez des Gombos",careers:"Carrières","media-press":"Médias & Presse","lottery-participation":"Participation aux tirages spéciaux","intellectual-property":"Propriété Intellectuelle","order-delivery":"Livraison des commandes","report-suspicious":"Signaler une activité suspecte","support-center":"Centre d'assistance","security-center":"Centre de sécurité","peace-on-koumbaya":"Avoir la paix sur Koumbaya",sitemap:"Plan du site","sell-on-koumbaya":"Vendre sur Koumbaya",terms:"Conditions d'utilisation",privacy:"Politique de confidentialité",legal:"Mentions légales",cookies:"Politique de cookies"})[s.value]||"Page Koumbaya"),h=t(()=>["sell-on-koumbaya","affiliates","careers"].includes(s.value)),_=t(()=>({"sell-on-koumbaya":"Commencer à vendre",affiliates:"Rejoindre le programme",careers:"Voir les offres d'emploi"})[s.value]||"En savoir plus"),y=t(()=>({"sell-on-koumbaya":"/register",affiliates:"/register",careers:"/contact"})[s.value]||"/contact"),l=t(()=>({about:[{path:"/how-it-works",title:"Comment ça marche",description:"Découvrez le fonctionnement de Koumbaya"},{path:"/contact",title:"Contactez-nous",description:"Une question ? Nous sommes là pour vous"},{path:"/careers",title:"Carrières",description:"Rejoignez notre équipe"}],"support-center":[{path:"/how-it-works",title:"FAQ",description:"Questions fréquemment posées"},{path:"/contact",title:"Contact",description:"Parlez à notre équipe support"},{path:"/report-suspicious",title:"Signaler un problème",description:"Signalez une activité suspecte"}],legal:[{path:"/terms",title:"CGU",description:"Conditions générales d'utilisation"},{path:"/privacy",title:"Confidentialité",description:"Protection de vos données"},{path:"/cookies",title:"Cookies",description:"Politique de cookies"}]})[s.value]||[]);return(o,d)=>{const u=k("router-link");return a(),i("div",N,[e("section",V,[e("div",K,[e("h1",L,n(v.value),1),p.value?(a(),i("p",S,n(p.value),1)):c("",!0)])]),e("section",T,[e("div",A,[e("div",B,[e("div",M,[e("div",{class:"prose prose-lg max-w-none",innerHTML:g.value},null,8,D)])]),h.value?(a(),i("div",F,[f(u,{to:y.value,class:"inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-xl text-white bg-[#0099cc] hover:bg-[#0088bb] transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"},{default:m(()=>[P(n(_.value),1)]),_:1},8,["to"])])):c("",!0)])]),l.value&&l.value.length>0?(a(),i("section",G,[e("div",I,[d[0]||(d[0]=e("h2",{class:"text-2xl md:text-3xl font-bold text-gray-900 text-center mb-12"}," Pages connexes ",-1)),e("div",R,[(a(!0),i(C,null,w(l.value,r=>(a(),q(u,{key:r.path,to:r.path,class:"bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-200 hover:scale-[1.02]"},{default:m(()=>[e("h3",j,n(r.title),1),e("p",E,n(r.description),1)]),_:2},1032,["to"]))),128))])])])):c("",!0)])}}},O=z(H,[["__scopeId","data-v-44109650"]]);export{O as default};

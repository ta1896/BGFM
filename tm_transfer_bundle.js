var playerTransferHistory=(function(){"use strict";var pd=Object.defineProperty;var Gl=Kn=>{throw TypeError(Kn)};var yd=(Kn,$n,Gn)=>$n in Kn?pd(Kn,$n,{enumerable:!0,configurable:!0,writable:!0,value:Gn}):Kn[$n]=Gn;var ae=(Kn,$n,Gn)=>yd(Kn,typeof $n!="symbol"?$n+"":$n,Gn),Do=(Kn,$n,Gn)=>$n.has(Kn)||Gl("Cannot "+Gn);var F=(Kn,$n,Gn)=>(Do(Kn,$n,"read from private field"),Gn?Gn.call(Kn):$n.get(Kn)),An=(Kn,$n,Gn)=>$n.has(Kn)?Gl("Cannot add the same private member more than once"):$n instanceof WeakSet?$n.add(Kn):$n.set(Kn,Gn),un=(Kn,$n,Gn,Kr)=>(Do(Kn,$n,"write to private field"),Kr?Kr.call(Kn,Gn):$n.set(Kn,Gn),Gn),ce=(Kn,$n,Gn)=>(Do(Kn,$n,"access private method"),Gn);var ml,Er,Sr,Ut,ei,jr,Tr,er,Ir,ti,ri,Ve,Ro,ga,$o,Ze,Me,ii,at,tr,ot,He,Ae,st,Et,rr,St,ir,jt,Ni,Li,me,Yl,Wl,ba,_a,Mo,pt,Ge;typeof window<"u"&&((ml=window.__svelte??(window.__svelte={})).v??(ml.v=new Set)).add("5");const $n=1,Gn=2,Kr=4,Xl=8,Jl=16,Ql=1,Zl=2,nf=4,ef=8,tf=16,rf=1,af=2,Fo="[",wi="[!",xa="]",vr={},de=Symbol(),of="http://www.w3.org/1999/xhtml",sf="http://www.w3.org/2000/svg",Bo=!1;var ka=Array.isArray,lf=Array.prototype.indexOf,Pa=Array.from,gi=Object.keys,pr=Object.defineProperty,Bt=Object.getOwnPropertyDescriptor,No=Object.getOwnPropertyDescriptors,ff=Object.prototype,uf=Array.prototype,Ua=Object.getPrototypeOf,Lo=Object.isExtensible;const cf=()=>{};function df(t){return t()}function Ea(t){for(var r=0;r<t.length;r++)t[r]()}function Ko(){var t,r,i=new Promise((s,f)=>{t=s,r=f});return{promise:i,resolve:t,reject:r}}const je=2,Sa=4,bi=8,Nt=16,ht=32,gt=64,ja=128,Ne=256,_i=512,pe=1024,Ce=2048,bt=4096,Xe=8192,Lt=16384,Ta=32768,qr=65536,qo=1<<17,hf=1<<18,Kt=1<<19,Vo=1<<20,Ia=1<<21,xi=1<<22,qt=1<<23,Vt=Symbol("$state"),Ho=Symbol("legacy props"),mf=Symbol(""),Vr=new class extends Error{constructor(){super(...arguments);ae(this,"name","StaleReactionError");ae(this,"message","The reaction that called `getAbortSignal()` was re-run or destroyed")}},vf=1,ki=3,yr=8;function pf(t){throw new Error("https://svelte.dev/e/lifecycle_outside_component")}function yf(){throw new Error("https://svelte.dev/e/async_derived_orphan")}function wf(t){throw new Error("https://svelte.dev/e/effect_in_teardown")}function gf(){throw new Error("https://svelte.dev/e/effect_in_unowned_derived")}function bf(t){throw new Error("https://svelte.dev/e/effect_orphan")}function _f(){throw new Error("https://svelte.dev/e/effect_update_depth_exceeded")}function xf(){throw new Error("https://svelte.dev/e/hydration_failed")}function kf(t){throw new Error("https://svelte.dev/e/props_invalid_value")}function Pf(){throw new Error("https://svelte.dev/e/state_descriptors_fixed")}function Uf(){throw new Error("https://svelte.dev/e/state_prototype_fixed")}function Ef(){throw new Error("https://svelte.dev/e/state_unsafe_mutation")}function Sf(){throw new Error("https://svelte.dev/e/svelte_boundary_reset_onerror")}function Pi(t){console.warn("https://svelte.dev/e/hydration_mismatch")}function jf(){console.warn("https://svelte.dev/e/svelte_boundary_reset_noop")}let cn=!1;function ze(t){cn=t}let gn;function oe(t){if(t===null)throw Pi(),vr;return gn=t}function wr(){return oe(tt(gn))}function hn(t){if(cn){if(tt(gn)!==null)throw Pi(),vr;gn=t}}function Oa(t=1){if(cn){for(var r=t,i=gn;r--;)i=tt(i);gn=i}}function Ui(t=!0){for(var r=0,i=gn;;){if(i.nodeType===yr){var s=i.data;if(s===xa){if(r===0)return i;r-=1}else(s===Fo||s===wi)&&(r+=1)}var f=tt(i);t&&i.remove(),i=f}}function Go(t){if(!t||t.nodeType!==yr)throw Pi(),vr;return t.data}function Yo(t){return t===this.v}function Tf(t,r){return t!=t?r==r:t!==r||t!==null&&typeof t=="object"||typeof t=="function"}function Wo(t){return!Tf(t,this.v)}let gr=!1,If=!1;function Of(){gr=!0}let Qn=null;function br(t){Qn=t}function se(t,r=!1,i){Qn={p:Qn,c:null,e:null,s:t,x:null,l:gr&&!r?{s:null,u:null,$:[]}:null}}function le(t){var r=Qn,i=r.e;if(i!==null){r.e=null;for(var s of i)vs(s)}return t!==void 0&&(r.x=t),Qn=r.p,t??{}}function Hr(){return!gr||Qn!==null&&Qn.l===null}let Ht=[];function Xo(){var t=Ht;Ht=[],Ea(t)}function Gt(t){if(Ht.length===0&&!Gr){var r=Ht;queueMicrotask(()=>{r===Ht&&Xo()})}Ht.push(t)}function Af(){for(;Ht.length>0;)Xo()}const Cf=new WeakMap;function Jo(t){var r=_n;if(r===null)return kn.f|=qt,t;if((r.f&Ta)===0){if((r.f&ja)===0)throw!r.parent&&t instanceof Error&&Qo(t),t;r.b.error(t)}else _r(t,r)}function _r(t,r){for(;r!==null;){if((r.f&ja)!==0)try{r.b.error(t);return}catch(i){t=i}r=r.parent}throw t instanceof Error&&Qo(t),t}function Qo(t){const r=Cf.get(t);r&&(pr(t,"message",{value:r.message}),pr(t,"stack",{value:r.stack}))}const Ei=new Set;let Zn=null,Aa=new Set,nt=[],Si=null,Ca=!1,Gr=!1;const Or=class Or{constructor(){An(this,Ve);ae(this,"current",new Map);An(this,Er,new Map);An(this,Sr,new Set);An(this,Ut,0);An(this,ei,null);An(this,jr,[]);An(this,Tr,[]);An(this,er,[]);An(this,Ir,[]);An(this,ti,[]);An(this,ri,[]);ae(this,"skipped_effects",new Set)}process(r){var c;nt=[];var i=Or.apply(this);for(const d of r)ce(this,Ve,Ro).call(this,d);if(F(this,Ut)===0){ce(this,Ve,$o).call(this);var s=F(this,Tr),f=F(this,er);un(this,Tr,[]),un(this,er,[]),un(this,Ir,[]),Zn=null,ns(s),ns(f),(c=F(this,ei))==null||c.resolve()}else ce(this,Ve,ga).call(this,F(this,Tr)),ce(this,Ve,ga).call(this,F(this,er)),ce(this,Ve,ga).call(this,F(this,Ir));i();for(const d of F(this,jr))Jr(d);un(this,jr,[])}capture(r,i){F(this,Er).has(r)||F(this,Er).set(r,i),this.current.set(r,r.v)}activate(){Zn=this}deactivate(){Zn=null;for(const r of Aa)if(Aa.delete(r),r(),Zn!==null)break}flush(){if(nt.length>0){if(this.activate(),Zo(),Zn!==null&&Zn!==this)return}else F(this,Ut)===0&&ce(this,Ve,$o).call(this);this.deactivate()}increment(){un(this,Ut,F(this,Ut)+1)}decrement(){if(un(this,Ut,F(this,Ut)-1),F(this,Ut)===0){for(const r of F(this,ti))Ue(r,Ce),Yt(r);for(const r of F(this,ri))Ue(r,bt),Yt(r);this.flush()}else this.deactivate()}add_callback(r){F(this,Sr).add(r)}settled(){return(F(this,ei)??un(this,ei,Ko())).promise}static ensure(){if(Zn===null){const r=Zn=new Or;Ei.add(Zn),Gr||Or.enqueue(()=>{Zn===r&&r.flush()})}return Zn}static enqueue(r){Gt(r)}static apply(r){return cf}};Er=new WeakMap,Sr=new WeakMap,Ut=new WeakMap,ei=new WeakMap,jr=new WeakMap,Tr=new WeakMap,er=new WeakMap,Ir=new WeakMap,ti=new WeakMap,ri=new WeakMap,Ve=new WeakSet,Ro=function(r){var w;r.f^=pe;for(var i=r.first;i!==null;){var s=i.f,f=(s&(ht|gt))!==0,c=f&&(s&pe)!==0,d=c||(s&Xe)!==0||this.skipped_effects.has(i);if(!d&&i.fn!==null){f?i.f^=pe:(s&Sa)!==0?F(this,er).push(i):(s&pe)===0&&((s&xi)!==0&&((w=i.b)!=null&&w.is_pending())?F(this,jr).push(i):Ii(i)&&((i.f&Nt)!==0&&F(this,Ir).push(i),Jr(i)));var m=i.first;if(m!==null){i=m;continue}}var p=i.parent;for(i=i.next;i===null&&p!==null;)i=p.next,p=p.parent}},ga=function(r){for(const i of r)((i.f&Ce)!==0?F(this,ti):F(this,ri)).push(i),Ue(i,pe);r.length=0},$o=function(){var r;for(const i of F(this,Sr))i();if(F(this,Sr).clear(),Ei.size>1){F(this,Er).clear();let i=!0;for(const s of Ei){if(s===this){i=!1;continue}for(const[f,c]of this.current){if(s.current.has(f))if(i)s.current.set(f,c);else continue;es(f)}if(nt.length>0){Zn=s;const f=Or.apply(s);for(const c of nt)ce(r=s,Ve,Ro).call(r,c);nt=[],f()}}Zn=null}Ei.delete(this)};let et=Or;function mn(t){var r=Gr;Gr=!0;try{for(var i;;){if(Af(),nt.length===0&&(Zn==null||Zn.flush(),nt.length===0))return Si=null,i;Zo()}}finally{Gr=r}}function Zo(){var t=kr;Ca=!0;try{var r=0;for(xs(!0);nt.length>0;){var i=et.ensure();if(r++>1e3){var s,f;zf()}i.process(nt),_t.clear()}}finally{Ca=!1,xs(t),Si=null}}function zf(){try{_f()}catch(t){_r(t,Si)}}let mt=null;function ns(t){var r=t.length;if(r!==0){for(var i=0;i<r;){var s=t[i++];if((s.f&(Lt|Xe))===0&&Ii(s)&&(mt=[],Jr(s),s.deps===null&&s.first===null&&s.nodes_start===null&&(s.teardown===null&&s.ac===null?gs(s):s.fn=null),(mt==null?void 0:mt.length)>0)){_t.clear();for(const f of mt)Jr(f);mt=[]}}mt=null}}function es(t){if(t.reactions!==null)for(const r of t.reactions){const i=r.f;(i&je)!==0?es(r):(i&(xi|Nt))!==0&&(Ue(r,Ce),Yt(r))}}function Yt(t){for(var r=Si=t;r.parent!==null;){r=r.parent;var i=r.f;if(Ca&&r===_n&&(i&Nt)!==0)return;if((i&(gt|ht))!==0){if((i&pe)===0)return;r.f^=pe}}nt.push(r)}function Df(t){let r=0,i=Wt(0),s;return()=>{Vf()&&(V(i),Fa(()=>(r===0&&(s=nr(()=>t(()=>Wr(i)))),r+=1,()=>{Gt(()=>{r-=1,r===0&&(s==null||s(),s=void 0,Wr(i))})})))}}var Rf=qr|Kt|ja;function $f(t,r,i){new Mf(t,r,i)}class Mf{constructor(r,i,s){An(this,me);ae(this,"parent");An(this,Ze,!1);An(this,Me);An(this,ii,cn?gn:null);An(this,at);An(this,tr);An(this,ot);An(this,He,null);An(this,Ae,null);An(this,st,null);An(this,Et,null);An(this,rr,0);An(this,St,0);An(this,ir,!1);An(this,jt,null);An(this,Ni,()=>{F(this,jt)&&xr(F(this,jt),F(this,rr))});An(this,Li,Df(()=>(un(this,jt,Wt(F(this,rr))),()=>{un(this,jt,null)})));un(this,Me,r),un(this,at,i),un(this,tr,s),this.parent=_n.b,un(this,Ze,!!F(this,at).pending),un(this,ot,ji(()=>{if(_n.b=this,cn){const f=F(this,ii);wr(),f.nodeType===yr&&f.data===wi?ce(this,me,Wl).call(this):ce(this,me,Yl).call(this)}else{try{un(this,He,De(()=>s(F(this,Me))))}catch(f){this.error(f)}F(this,St)>0?ce(this,me,_a).call(this):un(this,Ze,!1)}},Rf)),cn&&un(this,Me,gn)}is_pending(){return F(this,Ze)||!!this.parent&&this.parent.is_pending()}has_pending_snippet(){return!!F(this,at).pending}update_pending_count(r){ce(this,me,Mo).call(this,r),un(this,rr,F(this,rr)+r),Aa.add(F(this,Ni))}get_effect_pending(){return F(this,Li).call(this),V(F(this,jt))}error(r){var i=F(this,at).onerror;let s=F(this,at).failed;if(F(this,ir)||!i&&!s)throw r;F(this,He)&&(Te(F(this,He)),un(this,He,null)),F(this,Ae)&&(Te(F(this,Ae)),un(this,Ae,null)),F(this,st)&&(Te(F(this,st)),un(this,st,null)),cn&&(oe(F(this,ii)),Oa(),oe(Ui()));var f=!1,c=!1;const d=()=>{if(f){jf();return}f=!0,c&&Sf(),et.ensure(),un(this,rr,0),F(this,st)!==null&&Jt(F(this,st),()=>{un(this,st,null)}),un(this,Ze,this.has_pending_snippet()),un(this,He,ce(this,me,ba).call(this,()=>(un(this,ir,!1),De(()=>F(this,tr).call(this,F(this,Me)))))),F(this,St)>0?ce(this,me,_a).call(this):un(this,Ze,!1)};var m=kn;try{Ie(null),c=!0,i==null||i(r,d),c=!1}catch(p){_r(p,F(this,ot)&&F(this,ot).parent)}finally{Ie(m)}s&&Gt(()=>{un(this,st,ce(this,me,ba).call(this,()=>{un(this,ir,!0);try{return De(()=>{s(F(this,Me),()=>r,()=>d)})}catch(p){return _r(p,F(this,ot).parent),null}finally{un(this,ir,!1)}}))})}}Ze=new WeakMap,Me=new WeakMap,ii=new WeakMap,at=new WeakMap,tr=new WeakMap,ot=new WeakMap,He=new WeakMap,Ae=new WeakMap,st=new WeakMap,Et=new WeakMap,rr=new WeakMap,St=new WeakMap,ir=new WeakMap,jt=new WeakMap,Ni=new WeakMap,Li=new WeakMap,me=new WeakSet,Yl=function(){try{un(this,He,De(()=>F(this,tr).call(this,F(this,Me))))}catch(r){this.error(r)}un(this,Ze,!1)},Wl=function(){const r=F(this,at).pending;r&&(un(this,Ae,De(()=>r(F(this,Me)))),et.enqueue(()=>{un(this,He,ce(this,me,ba).call(this,()=>(et.ensure(),De(()=>F(this,tr).call(this,F(this,Me)))))),F(this,St)>0?ce(this,me,_a).call(this):(Jt(F(this,Ae),()=>{un(this,Ae,null)}),un(this,Ze,!1))}))},ba=function(r){var i=_n,s=kn,f=Qn;rt(F(this,ot)),Ie(F(this,ot)),br(F(this,ot).ctx);try{return r()}catch(c){return Jo(c),null}finally{rt(i),Ie(s),br(f)}},_a=function(){const r=F(this,at).pending;F(this,He)!==null&&(un(this,Et,document.createDocumentFragment()),Ff(F(this,He),F(this,Et))),F(this,Ae)===null&&un(this,Ae,De(()=>r(F(this,Me))))},Mo=function(r){var i;if(!this.has_pending_snippet()){this.parent&&ce(i=this.parent,me,Mo).call(i,r);return}un(this,St,F(this,St)+r),F(this,St)===0&&(un(this,Ze,!1),F(this,Ae)&&Jt(F(this,Ae),()=>{un(this,Ae,null)}),F(this,Et)&&(F(this,Me).before(F(this,Et)),un(this,Et,null)),Gt(()=>{et.ensure().flush()}))};function Ff(t,r){for(var i=t.nodes_start,s=t.nodes_end;i!==null;){var f=i===s?null:tt(i);r.append(i),i=f}}function Bf(t,r,i){const s=Hr()?Yr:za;if(r.length===0){i(t.map(s));return}var f=Zn,c=_n,d=Nf(),m=cn;Promise.all(r.map(p=>Lf(p))).then(p=>{f==null||f.activate(),d();try{i([...t.map(s),...p])}catch(w){(c.f&Lt)===0&&_r(w,c)}m&&ze(!1),f==null||f.deactivate(),ts()}).catch(p=>{_r(p,c)})}function Nf(){var t=_n,r=kn,i=Qn,s=Zn,f=cn;if(f)var c=gn;return function(){rt(t),Ie(r),br(i),s==null||s.activate(),f&&(ze(!0),oe(c))}}function ts(){rt(null),Ie(null),br(null)}function Yr(t){var r=je|Ce,i=kn!==null&&(kn.f&je)!==0?kn:null;return _n===null||i!==null&&(i.f&Ne)!==0?r|=Ne:_n.f|=Kt,{ctx:Qn,deps:null,effects:null,equals:Yo,f:r,fn:t,reactions:null,rv:0,v:de,wv:0,parent:i??_n,ac:null}}function Lf(t,r){let i=_n;i===null&&yf();var s=i.b,f=void 0,c=Wt(de),d=!kn,m=new Map;return Wf(()=>{var j;var p=Ko();f=p.promise;try{Promise.resolve(t()).then(p.resolve,p.reject)}catch(D){p.reject(D)}var w=Zn,I=s.is_pending();d&&(s.update_pending_count(1),I||(w.increment(),(j=m.get(w))==null||j.reject(Vr),m.set(w,p)));const T=(D,C=void 0)=>{I||w.activate(),C?C!==Vr&&(c.f|=qt,xr(c,C)):((c.f&qt)!==0&&(c.f^=qt),xr(c,D)),d&&(s.update_pending_count(-1),I||w.decrement()),ts()};p.promise.then(T,D=>T(null,D||"unknown"))}),ms(()=>{for(const p of m.values())p.reject(Vr)}),new Promise(p=>{function w(I){function T(){I===f?p(c):w(f)}I.then(T,T)}w(f)})}function ne(t){const r=Yr(t);return Ps(r),r}function za(t){const r=Yr(t);return r.equals=Wo,r}function rs(t){var r=t.effects;if(r!==null){t.effects=null;for(var i=0;i<r.length;i+=1)Te(r[i])}}function Kf(t){for(var r=t.parent;r!==null;){if((r.f&je)===0)return r;r=r.parent}return null}function Da(t){var r,i=_n;rt(Kf(t));try{rs(t),r=Ts(t)}finally{rt(i)}return r}function is(t){var r=Da(t);if(t.equals(r)||(t.v=r,t.wv=Ss()),!Qt){var i=(kt||(t.f&Ne)!==0)&&t.deps!==null?bt:pe;Ue(t,i)}}const _t=new Map;function Wt(t,r){var i={f:0,v:t,reactions:null,equals:Yo,rv:0,wv:0};return i}function ye(t,r){const i=Wt(t);return Ps(i),i}function as(t,r=!1,i=!0){var f;const s=Wt(t);return r||(s.equals=Wo),gr&&i&&Qn!==null&&Qn.l!==null&&((f=Qn.l).s??(f.s=[])).push(s),s}function we(t,r,i=!1){kn!==null&&(!Qe||(kn.f&qo)!==0)&&Hr()&&(kn.f&(je|Nt|xi|qo))!==0&&!(ke!=null&&ke.includes(t))&&Ef();let s=i?Xt(r):r;return xr(t,s)}function xr(t,r){if(!t.equals(r)){var i=t.v;Qt?_t.set(t,r):_t.set(t,i),t.v=r;var s=et.ensure();s.capture(t,i),(t.f&je)!==0&&((t.f&Ce)!==0&&Da(t),Ue(t,(t.f&Ne)===0?pe:bt)),t.wv=Ss(),os(t,Ce),Hr()&&_n!==null&&(_n.f&pe)!==0&&(_n.f&(ht|gt))===0&&(Ke===null?Qf([t]):Ke.push(t))}return r}function Wr(t){we(t,t.v+1)}function os(t,r){var i=t.reactions;if(i!==null)for(var s=Hr(),f=i.length,c=0;c<f;c++){var d=i[c],m=d.f;if(!(!s&&d===_n)){var p=(m&Ce)===0;p&&Ue(d,r),(m&je)!==0?os(d,bt):p&&((m&Nt)!==0&&mt!==null&&mt.push(d),Yt(d))}}}function Xt(t){if(typeof t!="object"||t===null||Vt in t)return t;const r=Ua(t);if(r!==ff&&r!==uf)return t;var i=new Map,s=ka(t),f=ye(0),c=Zt,d=m=>{if(Zt===c)return m();var p=kn,w=Zt;Ie(null),Es(c);var I=m();return Ie(p),Es(w),I};return s&&i.set("length",ye(t.length)),new Proxy(t,{defineProperty(m,p,w){(!("value"in w)||w.configurable===!1||w.enumerable===!1||w.writable===!1)&&Pf();var I=i.get(p);return I===void 0?I=d(()=>{var T=ye(w.value);return i.set(p,T),T}):we(I,w.value,!0),!0},deleteProperty(m,p){var w=i.get(p);if(w===void 0){if(p in m){const I=d(()=>ye(de));i.set(p,I),Wr(f)}}else we(w,de),Wr(f);return!0},get(m,p,w){var D;if(p===Vt)return t;var I=i.get(p),T=p in m;if(I===void 0&&(!T||(D=Bt(m,p))!=null&&D.writable)&&(I=d(()=>{var C=Xt(T?m[p]:de),$=ye(C);return $}),i.set(p,I)),I!==void 0){var j=V(I);return j===de?void 0:j}return Reflect.get(m,p,w)},getOwnPropertyDescriptor(m,p){var w=Reflect.getOwnPropertyDescriptor(m,p);if(w&&"value"in w){var I=i.get(p);I&&(w.value=V(I))}else if(w===void 0){var T=i.get(p),j=T==null?void 0:T.v;if(T!==void 0&&j!==de)return{enumerable:!0,configurable:!0,value:j,writable:!0}}return w},has(m,p){var j;if(p===Vt)return!0;var w=i.get(p),I=w!==void 0&&w.v!==de||Reflect.has(m,p);if(w!==void 0||_n!==null&&(!I||(j=Bt(m,p))!=null&&j.writable)){w===void 0&&(w=d(()=>{var D=I?Xt(m[p]):de,C=ye(D);return C}),i.set(p,w));var T=V(w);if(T===de)return!1}return I},set(m,p,w,I){var en;var T=i.get(p),j=p in m;if(s&&p==="length")for(var D=w;D<T.v;D+=1){var C=i.get(D+"");C!==void 0?we(C,de):D in m&&(C=d(()=>ye(de)),i.set(D+"",C))}if(T===void 0)(!j||(en=Bt(m,p))!=null&&en.writable)&&(T=d(()=>ye(void 0)),we(T,Xt(w)),i.set(p,T));else{j=T.v!==de;var $=d(()=>Xt(w));we(T,$)}var R=Reflect.getOwnPropertyDescriptor(m,p);if(R!=null&&R.set&&R.set.call(I,w),!j){if(s&&typeof p=="string"){var M=i.get("length"),W=Number(p);Number.isInteger(W)&&W>=M.v&&we(M,W+1)}Wr(f)}return!0},ownKeys(m){V(f);var p=Reflect.ownKeys(m).filter(T=>{var j=i.get(T);return j===void 0||j.v!==de});for(var[w,I]of i)I.v!==de&&!(w in m)&&p.push(w);return p},setPrototypeOf(){Uf()}})}var ss,ls,fs,us;function Ra(){if(ss===void 0){ss=window,ls=/Firefox/.test(navigator.userAgent);var t=Element.prototype,r=Node.prototype,i=Text.prototype;fs=Bt(r,"firstChild").get,us=Bt(r,"nextSibling").get,Lo(t)&&(t.__click=void 0,t.__className=void 0,t.__attributes=null,t.__style=void 0,t.__e=void 0),Lo(i)&&(i.__t=void 0)}}function Le(t=""){return document.createTextNode(t)}function xt(t){return fs.call(t)}function tt(t){return us.call(t)}function vn(t,r){if(!cn)return xt(t);var i=xt(gn);if(i===null)i=gn.appendChild(Le());else if(r&&i.nodeType!==ki){var s=Le();return i==null||i.before(s),oe(s),s}return oe(i),i}function ee(t,r=!1){if(!cn){var i=xt(t);return i instanceof Comment&&i.data===""?tt(i):i}if(r&&(gn==null?void 0:gn.nodeType)!==ki){var s=Le();return gn==null||gn.before(s),oe(s),s}return gn}function wn(t,r=1,i=!1){let s=cn?gn:t;for(var f;r--;)f=s,s=tt(s);if(!cn)return s;if(i&&(s==null?void 0:s.nodeType)!==ki){var c=Le();return s===null?f==null||f.after(c):s.before(c),oe(c),c}return oe(s),s}function cs(t){t.textContent=""}function ds(){return!1}function $a(t){var r=kn,i=_n;Ie(null),rt(null);try{return t()}finally{Ie(r),rt(i)}}function hs(t){_n===null&&kn===null&&bf(),kn!==null&&(kn.f&Ne)!==0&&_n===null&&gf(),Qt&&wf()}function qf(t,r){var i=r.last;i===null?r.last=r.first=t:(i.next=t,t.prev=i,r.last=t)}function Je(t,r,i,s=!0){var f=_n;f!==null&&(f.f&Xe)!==0&&(t|=Xe);var c={ctx:Qn,deps:null,nodes_start:null,nodes_end:null,f:t|Ce,first:null,fn:r,last:null,next:null,parent:f,b:f&&f.b,prev:null,teardown:null,transitions:null,wv:0,ac:null};if(i)try{Jr(c),c.f|=Ta}catch(p){throw Te(c),p}else r!==null&&Yt(c);if(s){var d=c;if(i&&d.deps===null&&d.teardown===null&&d.nodes_start===null&&d.first===d.last&&(d.f&Kt)===0&&(d=d.first),d!==null&&(d.parent=f,f!==null&&qf(d,f),kn!==null&&(kn.f&je)!==0&&(t&gt)===0)){var m=kn;(m.effects??(m.effects=[])).push(d)}}return c}function Vf(){return kn!==null&&!Qe}function ms(t){const r=Je(bi,null,!1);return Ue(r,pe),r.teardown=t,r}function Ma(t){hs();var r=_n.f,i=!kn&&(r&ht)!==0&&(r&Ta)===0;if(i){var s=Qn;(s.e??(s.e=[])).push(t)}else return vs(t)}function vs(t){return Je(Sa|Vo,t,!1)}function Hf(t){return hs(),Je(bi|Vo,t,!0)}function Gf(t){et.ensure();const r=Je(gt|Kt,t,!0);return()=>{Te(r)}}function Yf(t){et.ensure();const r=Je(gt|Kt,t,!0);return(i={})=>new Promise(s=>{i.outro?Jt(r,()=>{Te(r),s(void 0)}):(Te(r),s(void 0))})}function ps(t){return Je(Sa,t,!1)}function Wf(t){return Je(xi|Kt,t,!0)}function Fa(t,r=0){return Je(bi|r,t,!0)}function qn(t,r=[],i=[]){Bf(r,i,s=>{Je(bi,()=>t(...s.map(V)),!0)})}function ji(t,r=0){var i=Je(Nt|r,t,!0);return i}function De(t,r=!0){return Je(ht|Kt,t,!0,r)}function ys(t){var r=t.teardown;if(r!==null){const i=Qt,s=kn;ks(!0),Ie(null);try{r.call(null)}finally{ks(i),Ie(s)}}}function ws(t,r=!1){var i=t.first;for(t.first=t.last=null;i!==null;){const f=i.ac;f!==null&&$a(()=>{f.abort(Vr)});var s=i.next;(i.f&gt)!==0?i.parent=null:Te(i,r),i=s}}function Xf(t){for(var r=t.first;r!==null;){var i=r.next;(r.f&ht)===0&&Te(r),r=i}}function Te(t,r=!0){var i=!1;(r||(t.f&hf)!==0)&&t.nodes_start!==null&&t.nodes_end!==null&&(Jf(t.nodes_start,t.nodes_end),i=!0),ws(t,r&&!i),Oi(t,0),Ue(t,Lt);var s=t.transitions;if(s!==null)for(const c of s)c.stop();ys(t);var f=t.parent;f!==null&&f.first!==null&&gs(t),t.next=t.prev=t.teardown=t.ctx=t.deps=t.fn=t.nodes_start=t.nodes_end=t.ac=null}function Jf(t,r){for(;t!==null;){var i=t===r?null:tt(t);t.remove(),t=i}}function gs(t){var r=t.parent,i=t.prev,s=t.next;i!==null&&(i.next=s),s!==null&&(s.prev=i),r!==null&&(r.first===t&&(r.first=s),r.last===t&&(r.last=i))}function Jt(t,r){var i=[];Ba(t,i,!0),bs(i,()=>{Te(t),r&&r()})}function bs(t,r){var i=t.length;if(i>0){var s=()=>--i||r();for(var f of t)f.out(s)}else r()}function Ba(t,r,i){if((t.f&Xe)===0){if(t.f^=Xe,t.transitions!==null)for(const d of t.transitions)(d.is_global||i)&&r.push(d);for(var s=t.first;s!==null;){var f=s.next,c=(s.f&qr)!==0||(s.f&ht)!==0;Ba(s,r,c?i:!1),s=f}}}function Ti(t){_s(t,!0)}function _s(t,r){if((t.f&Xe)!==0){t.f^=Xe,(t.f&pe)===0&&(Ue(t,Ce),Yt(t));for(var i=t.first;i!==null;){var s=i.next,f=(i.f&qr)!==0||(i.f&ht)!==0;_s(i,f?r:!1),i=s}if(t.transitions!==null)for(const c of t.transitions)(c.is_global||r)&&c.in()}}let kr=!1;function xs(t){kr=t}let Qt=!1;function ks(t){Qt=t}let kn=null,Qe=!1;function Ie(t){kn=t}let _n=null;function rt(t){_n=t}let ke=null;function Ps(t){kn!==null&&(ke===null?ke=[t]:ke.push(t))}let Pe=null,Re=0,Ke=null;function Qf(t){Ke=t}let Us=1,Xr=0,Zt=Xr;function Es(t){Zt=t}let kt=!1;function Ss(){return++Us}function Ii(t){var T;var r=t.f;if((r&Ce)!==0)return!0;if((r&bt)!==0){var i=t.deps,s=(r&Ne)!==0;if(i!==null){var f,c,d=(r&_i)!==0,m=s&&_n!==null&&!kt,p=i.length;if((d||m)&&(_n===null||(_n.f&Lt)===0)){var w=t,I=w.parent;for(f=0;f<p;f++)c=i[f],(d||!((T=c==null?void 0:c.reactions)!=null&&T.includes(w)))&&(c.reactions??(c.reactions=[])).push(w);d&&(w.f^=_i),m&&I!==null&&(I.f&Ne)===0&&(w.f^=Ne)}for(f=0;f<p;f++)if(c=i[f],Ii(c)&&is(c),c.wv>t.wv)return!0}(!s||_n!==null&&!kt)&&Ue(t,pe)}return!1}function js(t,r,i=!0){var s=t.reactions;if(s!==null&&!(ke!=null&&ke.includes(t)))for(var f=0;f<s.length;f++){var c=s[f];(c.f&je)!==0?js(c,r,!1):r===c&&(i?Ue(c,Ce):(c.f&pe)!==0&&Ue(c,bt),Yt(c))}}function Ts(t){var $;var r=Pe,i=Re,s=Ke,f=kn,c=kt,d=ke,m=Qn,p=Qe,w=Zt,I=t.f;Pe=null,Re=0,Ke=null,kt=(I&Ne)!==0&&(Qe||!kr||kn===null),kn=(I&(ht|gt))===0?t:null,ke=null,br(t.ctx),Qe=!1,Zt=++Xr,t.ac!==null&&($a(()=>{t.ac.abort(Vr)}),t.ac=null);try{t.f|=Ia;var T=t.fn,j=T(),D=t.deps;if(Pe!==null){var C;if(Oi(t,Re),D!==null&&Re>0)for(D.length=Re+Pe.length,C=0;C<Pe.length;C++)D[Re+C]=Pe[C];else t.deps=D=Pe;if(!kt||(I&je)!==0&&t.reactions!==null)for(C=Re;C<D.length;C++)(($=D[C]).reactions??($.reactions=[])).push(t)}else D!==null&&Re<D.length&&(Oi(t,Re),D.length=Re);if(Hr()&&Ke!==null&&!Qe&&D!==null&&(t.f&(je|bt|Ce))===0)for(C=0;C<Ke.length;C++)js(Ke[C],t);return f!==null&&f!==t&&(Xr++,Ke!==null&&(s===null?s=Ke:s.push(...Ke))),(t.f&qt)!==0&&(t.f^=qt),j}catch(R){return Jo(R)}finally{t.f^=Ia,Pe=r,Re=i,Ke=s,kn=f,kt=c,ke=d,br(m),Qe=p,Zt=w}}function Zf(t,r){let i=r.reactions;if(i!==null){var s=lf.call(i,t);if(s!==-1){var f=i.length-1;f===0?i=r.reactions=null:(i[s]=i[f],i.pop())}}i===null&&(r.f&je)!==0&&(Pe===null||!Pe.includes(r))&&(Ue(r,bt),(r.f&(Ne|_i))===0&&(r.f^=_i),rs(r),Oi(r,0))}function Oi(t,r){var i=t.deps;if(i!==null)for(var s=r;s<i.length;s++)Zf(t,i[s])}function Jr(t){var r=t.f;if((r&Lt)===0){Ue(t,pe);var i=_n,s=kr;_n=t,kr=!0;try{(r&Nt)!==0?Xf(t):ws(t),ys(t);var f=Ts(t);t.teardown=typeof f=="function"?f:null,t.wv=Us;var c;Bo&&If&&(t.f&Ce)!==0&&t.deps}finally{kr=s,_n=i}}}function V(t){var r=t.f,i=(r&je)!==0;if(kn!==null&&!Qe){var s=_n!==null&&(_n.f&Lt)!==0;if(!s&&!(ke!=null&&ke.includes(t))){var f=kn.deps;if((kn.f&Ia)!==0)t.rv<Xr&&(t.rv=Xr,Pe===null&&f!==null&&f[Re]===t?Re++:Pe===null?Pe=[t]:(!kt||!Pe.includes(t))&&Pe.push(t));else{(kn.deps??(kn.deps=[])).push(t);var c=t.reactions;c===null?t.reactions=[kn]:c.includes(kn)||c.push(kn)}}}else if(i&&t.deps===null&&t.effects===null){var d=t,m=d.parent;m!==null&&(m.f&Ne)===0&&(d.f^=Ne)}if(Qt){if(_t.has(t))return _t.get(t);if(i){d=t;var p=d.v;return((d.f&pe)===0&&d.reactions!==null||Is(d))&&(p=Da(d)),_t.set(d,p),p}}else i&&(d=t,Ii(d)&&is(d));if((t.f&qt)!==0)throw t.v;return t.v}function Is(t){if(t.v===de)return!0;if(t.deps===null)return!1;for(const r of t.deps)if(_t.has(r)||(r.f&je)!==0&&Is(r))return!0;return!1}function nr(t){var r=Qe;try{return Qe=!0,t()}finally{Qe=r}}const nu=-7169;function Ue(t,r){t.f=t.f&nu|r}function Na(t){if(!(typeof t!="object"||!t||t instanceof EventTarget)){if(Vt in t)La(t);else if(!Array.isArray(t))for(let r in t){const i=t[r];typeof i=="object"&&i&&Vt in i&&La(i)}}}function La(t,r=new Set){if(typeof t=="object"&&t!==null&&!(t instanceof EventTarget)&&!r.has(t)){r.add(t),t instanceof Date&&t.getTime();for(let s in t)try{La(t[s],r)}catch{}const i=Ua(t);if(i!==Object.prototype&&i!==Array.prototype&&i!==Map.prototype&&i!==Set.prototype&&i!==Date.prototype){const s=No(i);for(let f in s){const c=s[f].get;if(c)try{c.call(t)}catch{}}}}}const eu=new Set,Os=new Set;function As(t){if(!cn)return;t.removeAttribute("onload"),t.removeAttribute("onerror");const r=t.__e;r!==void 0&&(t.__e=void 0,queueMicrotask(()=>{t.isConnected&&t.dispatchEvent(r)}))}function tu(t,r,i,s={}){function f(c){if(s.capture||Qr.call(r,c),!c.cancelBubble)return $a(()=>i==null?void 0:i.call(this,c))}return t.startsWith("pointer")||t.startsWith("touch")||t==="wheel"?Gt(()=>{r.addEventListener(t,f,s)}):r.addEventListener(t,f,s),f}function Cs(t,r,i,s,f){var c={capture:s,passive:f},d=tu(t,r,i,c);(r===document.body||r===window||r===document||r instanceof HTMLMediaElement)&&ms(()=>{r.removeEventListener(t,d,c)})}let zs=null;function Qr(t){var W;var r=this,i=r.ownerDocument,s=t.type,f=((W=t.composedPath)==null?void 0:W.call(t))||[],c=f[0]||t.target;zs=t;var d=0,m=zs===t&&t.__root;if(m){var p=f.indexOf(m);if(p!==-1&&(r===document||r===window)){t.__root=r;return}var w=f.indexOf(r);if(w===-1)return;p<=w&&(d=p)}if(c=f[d]||t.target,c!==r){pr(t,"currentTarget",{configurable:!0,get(){return c||i}});var I=kn,T=_n;Ie(null),rt(null);try{for(var j,D=[];c!==null;){var C=c.assignedSlot||c.parentNode||c.host||null;try{var $=c["__"+s];if($!=null&&(!c.disabled||t.target===c))if(ka($)){var[R,...M]=$;R.apply(c,[t,...M])}else $.call(c,t)}catch(en){j?D.push(en):j=en}if(t.cancelBubble||C===r||C===null)break;c=C}if(j){for(let en of D)queueMicrotask(()=>{throw en});throw j}}finally{t.__root=r,delete t.currentTarget,Ie(I),rt(T)}}}function ru(t){var r=document.createElement("template");return r.innerHTML=t.replaceAll("<!>","<!---->"),r.content}function vt(t,r){var i=_n;i.nodes_start===null&&(i.nodes_start=t,i.nodes_end=r)}function Mn(t,r){var i=(r&rf)!==0,s=(r&af)!==0,f,c=!t.startsWith("<!>");return()=>{if(cn)return vt(gn,null),gn;f===void 0&&(f=ru(c?t:"<!>"+t),i||(f=xt(f)));var d=s||ls?document.importNode(f,!0):f.cloneNode(!0);if(i){var m=xt(d),p=d.lastChild;vt(m,p)}else vt(d,d);return d}}function Pt(t=""){if(!cn){var r=Le(t+"");return vt(r,r),r}var i=gn;return i.nodeType!==ki&&(i.before(i=Le()),oe(i)),vt(i,i),i}function Oe(){if(cn)return vt(gn,null),gn;var t=document.createDocumentFragment(),r=document.createComment(""),i=Le();return t.append(r,i),vt(r,i),t}function on(t,r){if(cn){_n.nodes_end=gn,wr();return}t!==null&&t.before(r)}const iu=["touchstart","touchmove"];function au(t){return iu.includes(t)}const ou=["textarea","script","style","title"];function su(t){return ou.includes(t)}function Bn(t,r){var i=r==null?"":typeof r=="object"?r+"":r;i!==(t.__t??(t.__t=t.nodeValue))&&(t.__t=i,t.nodeValue=i+"")}function Ds(t,r){return Rs(t,r)}function lu(t,r){Ra(),r.intro=r.intro??!1;const i=r.target,s=cn,f=gn;try{for(var c=xt(i);c&&(c.nodeType!==yr||c.data!==Fo);)c=tt(c);if(!c)throw vr;ze(!0),oe(c);const d=Rs(t,{...r,anchor:c});return ze(!1),d}catch(d){if(d instanceof Error&&d.message.split(`
`).some(m=>m.startsWith("https://svelte.dev/e/")))throw d;return d!==vr&&console.warn("Failed to hydrate: ",d),r.recover===!1&&xf(),Ra(),cs(i),ze(!1),Ds(t,r)}finally{ze(s),oe(f)}}const Pr=new Map;function Rs(t,{target:r,anchor:i,props:s={},events:f,context:c,intro:d=!0}){Ra();var m=new Set,p=T=>{for(var j=0;j<T.length;j++){var D=T[j];if(!m.has(D)){m.add(D);var C=au(D);r.addEventListener(D,Qr,{passive:C});var $=Pr.get(D);$===void 0?(document.addEventListener(D,Qr,{passive:C}),Pr.set(D,1)):Pr.set(D,$+1)}}};p(Pa(eu)),Os.add(p);var w=void 0,I=Yf(()=>{var T=i??r.appendChild(Le());return $f(T,{pending:()=>{}},j=>{if(c){se({});var D=Qn;D.c=c}if(f&&(s.$$events=f),cn&&vt(j,null),w=t(j,s)||{},cn&&(_n.nodes_end=gn,gn===null||gn.nodeType!==yr||gn.data!==xa))throw Pi(),vr;c&&le()}),()=>{var C;for(var j of m){r.removeEventListener(j,Qr);var D=Pr.get(j);--D===0?(document.removeEventListener(j,Qr),Pr.delete(j)):Pr.set(j,D)}Os.delete(p),T!==i&&((C=T.parentNode)==null||C.removeChild(T))}});return Ka.set(w,I),w}let Ka=new WeakMap;function fu(t,r){const i=Ka.get(t);return i?(Ka.delete(t),i(r)):Promise.resolve()}function $s(t){Qn===null&&pf(),gr&&Qn.l!==null?uu(Qn).m.push(t):Ma(()=>{const r=nr(t);if(typeof r=="function")return r})}function uu(t){var r=t.l;return r.u??(r.u={a:[],b:[],m:[]})}function Fn(t,r,i=!1){cn&&wr();var s=t,f=null,c=null,d=de,m=i?qr:0,p=!1;const w=(D,C=!0)=>{p=!0,j(C,D)};var I=null;function T(){I!==null&&(I.lastChild.remove(),s.before(I),I=null);var D=d?f:c,C=d?c:f;D&&Ti(D),C&&Jt(C,()=>{d?c=null:f=null})}const j=(D,C)=>{if(d===(d=D))return;let $=!1;if(cn){const ln=Go(s)===wi;!!d===ln&&(s=Ui(),oe(s),ze(!1),$=!0)}var R=ds(),M=s;if(R&&(I=document.createDocumentFragment(),I.append(M=Le())),d?f??(f=C&&De(()=>C(M))):c??(c=C&&De(()=>C(M))),R){var W=Zn,en=d?f:c,q=d?c:f;en&&W.skipped_effects.delete(en),q&&W.skipped_effects.add(q),W.add_callback(T)}else T();$&&ze(!0)};ji(()=>{p=!1,r(w),p||j(null,null)},m),cn&&(s=gn)}function qa(t,r){return r}function cu(t,r,i){for(var s=t.items,f=[],c=r.length,d=0;d<c;d++)Ba(r[d].e,f,!0);var m=c>0&&f.length===0&&i!==null;if(m){var p=i.parentNode;cs(p),p.append(i),s.clear(),it(t,r[0].prev,r[c-1].next)}bs(f,()=>{for(var w=0;w<c;w++){var I=r[w];m||(s.delete(I.k),it(t,I.prev,I.next)),Te(I.e,!m)}})}function Va(t,r,i,s,f,c=null){var d=t,m={flags:r,items:new Map,first:null},p=(r&Kr)!==0;if(p){var w=t;d=cn?oe(xt(w)):w.appendChild(Le())}cn&&wr();var I=null,T=!1,j=new Map,D=za(()=>{var M=i();return ka(M)?M:M==null?[]:Pa(M)}),C,$;function R(){du($,C,m,j,d,f,r,s,i),c!==null&&(C.length===0?I?Ti(I):I=De(()=>c(d)):I!==null&&Jt(I,()=>{I=null}))}ji(()=>{$??($=_n),C=V(D);var M=C.length;if(T&&M===0)return;T=M===0;let W=!1;if(cn){var en=Go(d)===wi;en!==(M===0)&&(d=Ui(),oe(d),ze(!1),W=!0)}if(cn){for(var q=null,ln,H=0;H<M;H++){if(gn.nodeType===yr&&gn.data===xa){d=gn,W=!0,ze(!1);break}var Z=C[H],Pn=s(Z,H);ln=Ha(gn,m,q,null,Z,Pn,H,f,r,i),m.items.set(Pn,ln),q=ln}M>0&&oe(Ui())}if(cn)M===0&&c&&(I=De(()=>c(d)));else if(ds()){var Wn=new Set,Dn=Zn;for(H=0;H<M;H+=1){Z=C[H],Pn=s(Z,H);var Cn=m.items.get(Pn)??j.get(Pn);Cn?(r&($n|Gn))!==0&&Ms(Cn,Z,H,r):(ln=Ha(null,m,null,null,Z,Pn,H,f,r,i,!0),j.set(Pn,ln)),Wn.add(Pn)}for(const[zn,Un]of m.items)Wn.has(zn)||Dn.skipped_effects.add(Un.e);Dn.add_callback(R)}else R();W&&ze(!0),V(D)}),cn&&(d=gn)}function du(t,r,i,s,f,c,d,m,p){var nn,Nn,Xn,Q;var w=(d&Xl)!==0,I=(d&($n|Gn))!==0,T=r.length,j=i.items,D=i.first,C=D,$,R=null,M,W=[],en=[],q,ln,H,Z;if(w)for(Z=0;Z<T;Z+=1)q=r[Z],ln=m(q,Z),H=j.get(ln),H!==void 0&&((nn=H.a)==null||nn.measure(),(M??(M=new Set)).add(H));for(Z=0;Z<T;Z+=1){if(q=r[Z],ln=m(q,Z),H=j.get(ln),H===void 0){var Pn=s.get(ln);if(Pn!==void 0){s.delete(ln),j.set(ln,Pn);var Wn=R?R.next:C;it(i,R,Pn),it(i,Pn,Wn),Ga(Pn,Wn,f),R=Pn}else{var Dn=C?C.e.nodes_start:f;R=Ha(Dn,i,R,R===null?i.first:R.next,q,ln,Z,c,d,p)}j.set(ln,R),W=[],en=[],C=R.next;continue}if(I&&Ms(H,q,Z,d),(H.e.f&Xe)!==0&&(Ti(H.e),w&&((Nn=H.a)==null||Nn.unfix(),(M??(M=new Set)).delete(H))),H!==C){if($!==void 0&&$.has(H)){if(W.length<en.length){var Cn=en[0],zn;R=Cn.prev;var Un=W[0],bn=W[W.length-1];for(zn=0;zn<W.length;zn+=1)Ga(W[zn],Cn,f);for(zn=0;zn<en.length;zn+=1)$.delete(en[zn]);it(i,Un.prev,bn.next),it(i,R,Un),it(i,bn,Cn),C=Cn,R=bn,Z-=1,W=[],en=[]}else $.delete(H),Ga(H,C,f),it(i,H.prev,H.next),it(i,H,R===null?i.first:R.next),it(i,R,H),R=H;continue}for(W=[],en=[];C!==null&&C.k!==ln;)(C.e.f&Xe)===0&&($??($=new Set)).add(C),en.push(C),C=C.next;if(C===null)continue;H=C}W.push(H),R=H,C=H.next}if(C!==null||$!==void 0){for(var sn=$===void 0?[]:Pa($);C!==null;)(C.e.f&Xe)===0&&sn.push(C),C=C.next;var te=sn.length;if(te>0){var Rn=(d&Kr)!==0&&T===0?f:null;if(w){for(Z=0;Z<te;Z+=1)(Xn=sn[Z].a)==null||Xn.measure();for(Z=0;Z<te;Z+=1)(Q=sn[Z].a)==null||Q.fix()}cu(i,sn,Rn)}}w&&Gt(()=>{var an;if(M!==void 0)for(H of M)(an=H.a)==null||an.apply()}),t.first=i.first&&i.first.e,t.last=R&&R.e;for(var En of s.values())Te(En.e);s.clear()}function Ms(t,r,i,s){(s&$n)!==0&&xr(t.v,r),(s&Gn)!==0?xr(t.i,i):t.i=i}function Ha(t,r,i,s,f,c,d,m,p,w,I){var T=(p&$n)!==0,j=(p&Jl)===0,D=T?j?as(f,!1,!1):Wt(f):f,C=(p&Gn)===0?d:Wt(d),$={i:C,v:D,k:c,a:null,e:null,prev:i,next:s};try{if(t===null){var R=document.createDocumentFragment();R.append(t=Le())}return $.e=De(()=>m(t,D,C,w),cn),$.e.prev=i&&i.e,$.e.next=s&&s.e,i===null?I||(r.first=$):(i.next=$,i.e.next=$.e),s!==null&&(s.prev=$,s.e.prev=$.e),$}finally{}}function Ga(t,r,i){for(var s=t.next?t.next.e.nodes_start:i,f=r?r.e.nodes_start:i,c=t.e.nodes_start;c!==null&&c!==s;){var d=tt(c);f.before(c),c=d}}function it(t,r,i){r===null?t.first=i:(r.next=i,r.e.next=i&&i.e),i!==null&&(i.prev=r,i.e.prev=r&&r.e)}function hu(t,r,i,s,f,c){let d=cn;cn&&wr();var m,p,w=null;cn&&gn.nodeType===vf&&(w=gn,wr());var I=cn?gn:t,T;ji(()=>{const j=r()||null;var D=j==="svg"?sf:null;j!==m&&(T&&(j===null?Jt(T,()=>{T=null,p=null}):j===p?Ti(T):Te(T)),j&&j!==p&&(T=De(()=>{if(w=cn?w:D?document.createElementNS(D,j):document.createElement(j),vt(w,w),s){cn&&su(j)&&w.append(document.createComment(""));var C=cn?xt(w):w.appendChild(Le());cn&&(C===null?ze(!1):oe(C)),s(w,C)}_n.nodes_end=w,I.before(w)})),m=j,m&&(p=m))},qr),d&&(ze(!0),oe(I))}function $e(t,r){ps(()=>{var i=t.getRootNode(),s=i.host?i:i.head??i.ownerDocument.head;if(!s.querySelector("#"+r.hash)){const f=document.createElement("style");f.id=r.hash,f.textContent=r.code,s.appendChild(f)}})}function mu(t,r,i){var s=""+t;return s}function vu(t,r){return t==null?null:String(t)}function pu(t,r,i,s,f,c){var d=t.__className;if(cn||d!==i||d===void 0){var m=mu(i);(!cn||m!==t.getAttribute("class"))&&(m==null?t.removeAttribute("class"):t.setAttribute("class",m)),t.__className=i}return c}function Fs(t,r,i,s){var f=t.__style;if(cn||f!==r){var c=vu(r);(!cn||c!==t.getAttribute("style"))&&(c==null?t.removeAttribute("style"):t.style.cssText=c),t.__style=r}return s}const yu=Symbol("is custom element"),wu=Symbol("is html");function ge(t,r,i,s){var f=gu(t);cn&&(f[r]=t.getAttribute(r),r==="src"||r==="srcset"||r==="href"&&t.nodeName==="LINK")||f[r]!==(f[r]=i)&&(r==="loading"&&(t[mf]=i),i==null?t.removeAttribute(r):typeof i!="string"&&bu(t).includes(r)?t[r]=i:t.setAttribute(r,i))}function gu(t){return t.__attributes??(t.__attributes={[yu]:t.nodeName.includes("-"),[wu]:t.namespaceURI===of})}var Bs=new Map;function bu(t){var r=t.getAttribute("is")||t.nodeName,i=Bs.get(r);if(i)return i;Bs.set(r,i=[]);for(var s,f=t,c=Element.prototype;c!==f;){s=No(f);for(var d in s)s[d].set&&i.push(d);f=Ua(f)}return i}function Ns(t,r){return t===r||(t==null?void 0:t[Vt])===r}function _u(t={},r,i,s){return ps(()=>{var f,c;return Fa(()=>{f=c,c=[],nr(()=>{t!==i(...c)&&(r(t,...c),f&&Ns(i(...f),t)&&r(null,...f))})}),()=>{Gt(()=>{c&&Ns(i(...c),t)&&r(null,...c)})}}),t}function xu(t=!1){const r=Qn,i=r.l.u;if(!i)return;let s=()=>Na(r.s);if(t){let f=0,c={};const d=Yr(()=>{let m=!1;const p=r.s;for(const w in p)p[w]!==c[w]&&(c[w]=p[w],m=!0);return m&&f++,f});s=()=>V(d)}i.b.length&&Hf(()=>{Ls(r,s),Ea(i.b)}),Ma(()=>{const f=nr(()=>i.m.map(df));return()=>{for(const c of f)typeof c=="function"&&c()}}),i.a.length&&Ma(()=>{Ls(r,s),Ea(i.a)})}function Ls(t,r){if(t.l.s)for(const i of t.l.s)V(i);r()}let Ai=!1;function ku(t){var r=Ai;try{return Ai=!1,[t(),Ai]}finally{Ai=r}}function pn(t,r,i,s){var en;var f=!gr||(i&Zl)!==0,c=(i&ef)!==0,d=(i&tf)!==0,m=s,p=!0,w=()=>(p&&(p=!1,m=d?nr(s):s),m),I;if(c){var T=Vt in t||Ho in t;I=((en=Bt(t,r))==null?void 0:en.set)??(T&&r in t?q=>t[r]=q:void 0)}var j,D=!1;c?[j,D]=ku(()=>t[r]):j=t[r],j===void 0&&s!==void 0&&(j=w(),I&&(f&&kf(),I(j)));var C;if(f?C=()=>{var q=t[r];return q===void 0?w():(p=!0,q)}:C=()=>{var q=t[r];return q!==void 0&&(m=void 0),q===void 0?m:q},f&&(i&nf)===0)return C;if(I){var $=t.$$legacy;return(function(q,ln){return arguments.length>0?((!f||!ln||$||D)&&I(ln?C():q),q):C()})}var R=!1,M=((i&Ql)!==0?Yr:za)(()=>(R=!1,C()));c&&V(M);var W=_n;return(function(q,ln){if(arguments.length>0){const H=ln?V(M):f&&c?Xt(q):q;return we(M,H),R=!0,m!==void 0&&(m=H),q}return Qt&&R||(W.f&Lt)!==0?M.v:V(M)})}function Pu(t){return new Uu(t)}class Uu{constructor(r){An(this,pt);An(this,Ge);var c;var i=new Map,s=(d,m)=>{var p=as(m,!1,!1);return i.set(d,p),p};const f=new Proxy({...r.props||{},$$events:{}},{get(d,m){return V(i.get(m)??s(m,Reflect.get(d,m)))},has(d,m){return m===Ho?!0:(V(i.get(m)??s(m,Reflect.get(d,m))),Reflect.has(d,m))},set(d,m,p){return we(i.get(m)??s(m,p),p),Reflect.set(d,m,p)}});un(this,Ge,(r.hydrate?lu:Ds)(r.component,{target:r.target,anchor:r.anchor,props:f,context:r.context,intro:r.intro??!1,recover:r.recover})),(!((c=r==null?void 0:r.props)!=null&&c.$$host)||r.sync===!1)&&mn(),un(this,pt,f.$$events);for(const d of Object.keys(F(this,Ge)))d==="$set"||d==="$destroy"||d==="$on"||pr(this,d,{get(){return F(this,Ge)[d]},set(m){F(this,Ge)[d]=m},enumerable:!0});F(this,Ge).$set=d=>{Object.assign(f,d)},F(this,Ge).$destroy=()=>{fu(F(this,Ge))}}$set(r){F(this,Ge).$set(r)}$on(r,i){F(this,pt)[r]=F(this,pt)[r]||[];const s=(...f)=>i.call(this,...f);return F(this,pt)[r].push(s),()=>{F(this,pt)[r]=F(this,pt)[r].filter(f=>f!==s)}}$destroy(){F(this,Ge).$destroy()}}pt=new WeakMap,Ge=new WeakMap;let Ks;typeof HTMLElement=="function"&&(Ks=class extends HTMLElement{constructor(r,i,s){super();ae(this,"$$ctor");ae(this,"$$s");ae(this,"$$c");ae(this,"$$cn",!1);ae(this,"$$d",{});ae(this,"$$r",!1);ae(this,"$$p_d",{});ae(this,"$$l",{});ae(this,"$$l_u",new Map);ae(this,"$$me");this.$$ctor=r,this.$$s=i,s&&this.attachShadow({mode:"open"})}addEventListener(r,i,s){if(this.$$l[r]=this.$$l[r]||[],this.$$l[r].push(i),this.$$c){const f=this.$$c.$on(r,i);this.$$l_u.set(i,f)}super.addEventListener(r,i,s)}removeEventListener(r,i,s){if(super.removeEventListener(r,i,s),this.$$c){const f=this.$$l_u.get(i);f&&(f(),this.$$l_u.delete(i))}}async connectedCallback(){if(this.$$cn=!0,!this.$$c){let r=function(f){return c=>{const d=document.createElement("slot");f!=="default"&&(d.name=f),on(c,d)}};if(await Promise.resolve(),!this.$$cn||this.$$c)return;const i={},s=Eu(this);for(const f of this.$$s)f in s&&(f==="default"&&!this.$$d.children?(this.$$d.children=r(f),i.default=!0):i[f]=r(f));for(const f of this.attributes){const c=this.$$g_p(f.name);c in this.$$d||(this.$$d[c]=Ci(c,f.value,this.$$p_d,"toProp"))}for(const f in this.$$p_d)!(f in this.$$d)&&this[f]!==void 0&&(this.$$d[f]=this[f],delete this[f]);this.$$c=Pu({component:this.$$ctor,target:this.shadowRoot||this,props:{...this.$$d,$$slots:i,$$host:this}}),this.$$me=Gf(()=>{Fa(()=>{var f;this.$$r=!0;for(const c of gi(this.$$c)){if(!((f=this.$$p_d[c])!=null&&f.reflect))continue;this.$$d[c]=this.$$c[c];const d=Ci(c,this.$$d[c],this.$$p_d,"toAttribute");d==null?this.removeAttribute(this.$$p_d[c].attribute||c):this.setAttribute(this.$$p_d[c].attribute||c,d)}this.$$r=!1})});for(const f in this.$$l)for(const c of this.$$l[f]){const d=this.$$c.$on(f,c);this.$$l_u.set(c,d)}this.$$l={}}}attributeChangedCallback(r,i,s){var f;this.$$r||(r=this.$$g_p(r),this.$$d[r]=Ci(r,s,this.$$p_d,"toProp"),(f=this.$$c)==null||f.$set({[r]:this.$$d[r]}))}disconnectedCallback(){this.$$cn=!1,Promise.resolve().then(()=>{!this.$$cn&&this.$$c&&(this.$$c.$destroy(),this.$$me(),this.$$c=void 0)})}$$g_p(r){return gi(this.$$p_d).find(i=>this.$$p_d[i].attribute===r||!this.$$p_d[i].attribute&&i.toLowerCase()===r)||r}});function Ci(t,r,i,s){var c;const f=(c=i[t])==null?void 0:c.type;if(r=f==="Boolean"&&typeof r!="boolean"?r!=null:r,!s||!i[t])return r;if(s==="toAttribute")switch(f){case"Object":case"Array":return r==null?null:JSON.stringify(r);case"Boolean":return r?"":null;case"Number":return r??null;default:return r}else switch(f){case"Object":case"Array":return r&&JSON.parse(r);case"Boolean":return r;case"Number":return r!=null?+r:r;default:return r}}function Eu(t){const r={};return t.childNodes.forEach(i=>{r[i.slot||"default"]=!0}),r}function he(t,r,i,s,f,c){let d=class extends Ks{constructor(){super(t,i,f),this.$$p_d=r}static get observedAttributes(){return gi(r).map(m=>(r[m].attribute||m).toLowerCase())}};return gi(r).forEach(m=>{pr(d.prototype,m,{get(){return this.$$c&&m in this.$$c?this.$$c[m]:this.$$d[m]},set(p){var T;p=Ci(m,p,r),this.$$d[m]=p;var w=this.$$c;if(w){var I=(T=Bt(w,m))==null?void 0:T.get;I?w[m]=p:w.$set({[m]:p})}}})}),s.forEach(m=>{pr(d.prototype,m,{get(){var p;return(p=this.$$c)==null?void 0:p[m]}})}),t.element=d,d}const Su=/\{[^{}]+\}/g,ju=()=>{var t,r;return typeof process=="object"&&Number.parseInt((r=(t=process==null?void 0:process.versions)==null?void 0:t.node)==null?void 0:r.substring(0,2))>=18&&process.versions.undici};function Tu(){return Math.random().toString(36).slice(2,11)}function Iu(t){let{baseUrl:r="",Request:i=globalThis.Request,fetch:s=globalThis.fetch,querySerializer:f,bodySerializer:c,headers:d,requestInitExt:m=void 0,...p}={...t};m=ju()?m:void 0,r=Ys(r);const w=[];async function I(T,j){const{baseUrl:D,fetch:C=s,Request:$=i,headers:R,params:M={},parseAs:W="json",querySerializer:en,bodySerializer:q=c??Au,body:ln,...H}=j||{};let Z=r;D&&(Z=Ys(D)??r);let Pn=typeof f=="function"?f:Hs(f);en&&(Pn=typeof en=="function"?en:Hs({...typeof f=="object"?f:{},...en}));const Wn=ln===void 0?void 0:q(ln,Gs(d,R,M.header)),Dn=Gs(Wn===void 0||Wn instanceof FormData?{}:{"Content-Type":"application/json"},d,R,M.header),Cn={redirect:"follow",...p,...H,body:Wn,headers:Dn};let zn,Un,bn=new $(Cu(T,{baseUrl:Z,params:M,querySerializer:Pn}),Cn),sn;for(const Rn in H)Rn in bn||(bn[Rn]=H[Rn]);if(w.length){zn=Tu(),Un=Object.freeze({baseUrl:Z,fetch:C,parseAs:W,querySerializer:Pn,bodySerializer:q});for(const Rn of w)if(Rn&&typeof Rn=="object"&&typeof Rn.onRequest=="function"){const En=await Rn.onRequest({request:bn,schemaPath:T,params:M,options:Un,id:zn});if(En)if(En instanceof $)bn=En;else if(En instanceof Response){sn=En;break}else throw new Error("onRequest: must return new Request() or Response() when modifying the request")}}if(!sn){try{sn=await C(bn,m)}catch(Rn){let En=Rn;if(w.length)for(let nn=w.length-1;nn>=0;nn--){const Nn=w[nn];if(Nn&&typeof Nn=="object"&&typeof Nn.onError=="function"){const Xn=await Nn.onError({request:bn,error:En,schemaPath:T,params:M,options:Un,id:zn});if(Xn){if(Xn instanceof Response){En=void 0,sn=Xn;break}if(Xn instanceof Error){En=Xn;continue}throw new Error("onError: must return new Response() or instance of Error")}}}if(En)throw En}if(w.length)for(let Rn=w.length-1;Rn>=0;Rn--){const En=w[Rn];if(En&&typeof En=="object"&&typeof En.onResponse=="function"){const nn=await En.onResponse({request:bn,response:sn,schemaPath:T,params:M,options:Un,id:zn});if(nn){if(!(nn instanceof Response))throw new Error("onResponse: must return new Response() when modifying the response");sn=nn}}}}if(sn.status===204||bn.method==="HEAD"||sn.headers.get("Content-Length")==="0")return sn.ok?{data:void 0,response:sn}:{error:void 0,response:sn};if(sn.ok)return W==="stream"?{data:sn.body,response:sn}:{data:await sn[W](),response:sn};let te=await sn.text();try{te=JSON.parse(te)}catch{}return{error:te,response:sn}}return{request(T,j,D){return I(j,{...D,method:T.toUpperCase()})},GET(T,j){return I(T,{...j,method:"GET"})},PUT(T,j){return I(T,{...j,method:"PUT"})},POST(T,j){return I(T,{...j,method:"POST"})},DELETE(T,j){return I(T,{...j,method:"DELETE"})},OPTIONS(T,j){return I(T,{...j,method:"OPTIONS"})},HEAD(T,j){return I(T,{...j,method:"HEAD"})},PATCH(T,j){return I(T,{...j,method:"PATCH"})},TRACE(T,j){return I(T,{...j,method:"TRACE"})},use(...T){for(const j of T)if(j){if(typeof j!="object"||!("onRequest"in j||"onResponse"in j||"onError"in j))throw new Error("Middleware must be an object with one of `onRequest()`, `onResponse() or `onError()`");w.push(j)}},eject(...T){for(const j of T){const D=w.indexOf(j);D!==-1&&w.splice(D,1)}}}}function zi(t,r,i){if(r==null)return"";if(typeof r=="object")throw new Error("Deeply-nested arrays/objects aren’t supported. Provide your own `querySerializer()` to handle these.");return`${t}=${(i==null?void 0:i.allowReserved)===!0?r:encodeURIComponent(r)}`}function qs(t,r,i){if(!r||typeof r!="object")return"";const s=[],f={simple:",",label:".",matrix:";"}[i.style]||"&";if(i.style!=="deepObject"&&i.explode===!1){for(const m in r)s.push(m,i.allowReserved===!0?r[m]:encodeURIComponent(r[m]));const d=s.join(",");switch(i.style){case"form":return`${t}=${d}`;case"label":return`.${d}`;case"matrix":return`;${t}=${d}`;default:return d}}for(const d in r){const m=i.style==="deepObject"?`${t}[${d}]`:d;s.push(zi(m,r[d],i))}const c=s.join(f);return i.style==="label"||i.style==="matrix"?`${f}${c}`:c}function Vs(t,r,i){if(!Array.isArray(r))return"";if(i.explode===!1){const c={form:",",spaceDelimited:"%20",pipeDelimited:"|"}[i.style]||",",d=(i.allowReserved===!0?r:r.map(m=>encodeURIComponent(m))).join(c);switch(i.style){case"simple":return d;case"label":return`.${d}`;case"matrix":return`;${t}=${d}`;default:return`${t}=${d}`}}const s={simple:",",label:".",matrix:";"}[i.style]||"&",f=[];for(const c of r)i.style==="simple"||i.style==="label"?f.push(i.allowReserved===!0?c:encodeURIComponent(c)):f.push(zi(t,c,i));return i.style==="label"||i.style==="matrix"?`${s}${f.join(s)}`:f.join(s)}function Hs(t){return function(i){const s=[];if(i&&typeof i=="object")for(const f in i){const c=i[f];if(c!=null){if(Array.isArray(c)){if(c.length===0)continue;s.push(Vs(f,c,{style:"form",explode:!0,...t==null?void 0:t.array,allowReserved:(t==null?void 0:t.allowReserved)||!1}));continue}if(typeof c=="object"){s.push(qs(f,c,{style:"deepObject",explode:!0,...t==null?void 0:t.object,allowReserved:(t==null?void 0:t.allowReserved)||!1}));continue}s.push(zi(f,c,t))}}return s.join("&")}}function Ou(t,r){let i=t;for(const s of t.match(Su)??[]){let f=s.substring(1,s.length-1),c=!1,d="simple";if(f.endsWith("*")&&(c=!0,f=f.substring(0,f.length-1)),f.startsWith(".")?(d="label",f=f.substring(1)):f.startsWith(";")&&(d="matrix",f=f.substring(1)),!r||r[f]===void 0||r[f]===null)continue;const m=r[f];if(Array.isArray(m)){i=i.replace(s,Vs(f,m,{style:d,explode:c}));continue}if(typeof m=="object"){i=i.replace(s,qs(f,m,{style:d,explode:c}));continue}if(d==="matrix"){i=i.replace(s,`;${zi(f,m)}`);continue}i=i.replace(s,d==="label"?`.${encodeURIComponent(m)}`:encodeURIComponent(m))}return i}function Au(t,r){return t instanceof FormData?t:r&&(r.get instanceof Function?r.get("Content-Type")??r.get("content-type"):r["Content-Type"]??r["content-type"])==="application/x-www-form-urlencoded"?new URLSearchParams(t).toString():JSON.stringify(t)}function Cu(t,r){var f;let i=`${r.baseUrl}${t}`;(f=r.params)!=null&&f.path&&(i=Ou(i,r.params.path));let s=r.querySerializer(r.params.query??{});return s.startsWith("?")&&(s=s.substring(1)),s&&(i+=`?${s}`),i}function Gs(...t){const r=new Headers;for(const i of t){if(!i||typeof i!="object")continue;const s=i instanceof Headers?i.entries():Object.entries(i);for(const[f,c]of s)if(c===null)r.delete(f);else if(Array.isArray(c))for(const d of c)r.append(f,d);else c!==void 0&&r.set(f,c)}return r}function Ys(t){return t.endsWith("/")?t.substring(0,t.length-1):t}const Ws={at:"de-AT",ar:"es-AR",be:"nl-BE",br:"pt-BR",ch:"de-CH",co:"es-CO",com:"en-GB",de:"de-DE",es:"es-ES",fr:"fr-FR",gr:"el-GR",id:"id-ID",in:"en-IN",it:"it-IT",jp:"ja-JP",kr:"ko-KR",mx:"es-MX",nl:"nl-NL",pe:"es-PE",pl:"pl-PL",pt:"pt-PT",ro:"ro-RO",tr:"tr-TR",uk:"en-GB",us:"en-US",world:"ru-RU",za:"en-GB"},zu="com",Xs="en-US",Ya={accessToken:null,baseUrl:"https://tmapi-alpha.transfermarkt.technology/",useDomainContext:!1,useLocale:!1,signal:null},Js=(t={})=>{Ya.baseUrl=document.body.getAttribute("data-api-domain")??Ya.baseUrl;const r={...Ya,...t},i=document.body.getAttribute("data-tm-tld")??zu,s=Ws[i]??Xs,f=Iu({baseUrl:r.baseUrl,headers:{Accept:"application/json","Accept-Language":r.useLocale?s:Xs,...r.accessToken?{Authorization:`Bearer ${r.accessToken}`}:{}},...r.signal?{signal:r.signal}:{}});if(r.useDomainContext){const c={async onRequest({request:d}){const m=new URL(d.url);if(m.searchParams.set("_x_preferred_context",i),["POST","PUT","PATCH"].includes(d.method)){const p=await d.clone().text();return new Request(m.toString(),{method:d.method,headers:d.headers,body:p,mode:d.mode,credentials:d.credentials,cache:d.cache,redirect:d.redirect,referrer:d.referrer,referrerPolicy:d.referrerPolicy,integrity:d.integrity,keepalive:d.keepalive,signal:d.signal})}else return new Request(m.toString(),d)}};f.use(c)}return f},Du=async t=>{const r=Js({useLocale:!0}),{data:i,error:s}=await r.GET("/transfer/history/player/{playerId}",{params:{path:{playerId:t}}});return s||!i||!i.data?(console.error("Error fetching player transfer history",s),null):i.data},Ru=(t,r)=>{const i=new IntersectionObserver(s=>{(s[0].isIntersecting||s[0].intersectionRatio>0)&&(r(),i.disconnect())},{root:null,rootMargin:"200px 0px 200px 0px",threshold:1});return i.observe(t),i},$u=t=>{if(!t)return;const r=t.getRootNode();r instanceof ShadowRoot&&r.host instanceof HTMLElement?r.host.remove():t.parentElement&&t.parentElement.remove()};var Mu=typeof globalThis<"u"?globalThis:typeof window<"u"?window:typeof global<"u"?global:typeof self<"u"?self:{};function Fu(t){return t&&t.__esModule&&Object.prototype.hasOwnProperty.call(t,"default")?t.default:t}var Di={exports:{}},Bu=Di.exports,Qs;function Nu(){return Qs||(Qs=1,(function(t,r){(function(i,s){t.exports=s()})(Bu,function(){var i=function(n,e){return(i=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(a,o){a.__proto__=o}||function(a,o){for(var l in o)Object.prototype.hasOwnProperty.call(o,l)&&(a[l]=o[l])})(n,e)},s=function(){return(s=Object.assign||function(n){for(var e,a=1,o=arguments.length;a<o;a++)for(var l in e=arguments[a])Object.prototype.hasOwnProperty.call(e,l)&&(n[l]=e[l]);return n}).apply(this,arguments)};function f(n,e,a){for(var o,l=0,u=e.length;l<u;l++)!o&&l in e||((o=o||Array.prototype.slice.call(e,0,l))[l]=e[l]);return n.concat(o||Array.prototype.slice.call(e))}var c=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:Mu,d=Object.keys,m=Array.isArray;function p(n,e){return typeof e!="object"||d(e).forEach(function(a){n[a]=e[a]}),n}typeof Promise>"u"||c.Promise||(c.Promise=Promise);var w=Object.getPrototypeOf,I={}.hasOwnProperty;function T(n,e){return I.call(n,e)}function j(n,e){typeof e=="function"&&(e=e(w(n))),(typeof Reflect>"u"?d:Reflect.ownKeys)(e).forEach(function(a){C(n,a,e[a])})}var D=Object.defineProperty;function C(n,e,a,o){D(n,e,p(a&&T(a,"get")&&typeof a.get=="function"?{get:a.get,set:a.set,configurable:!0}:{value:a,configurable:!0,writable:!0},o))}function $(n){return{from:function(e){return n.prototype=Object.create(e.prototype),C(n.prototype,"constructor",n),{extend:j.bind(null,n.prototype)}}}}var R=Object.getOwnPropertyDescriptor,M=[].slice;function W(n,e,a){return M.call(n,e,a)}function en(n,e){return e(n)}function q(n){if(!n)throw new Error("Assertion Failed")}function ln(n){c.setImmediate?setImmediate(n):setTimeout(n,0)}function H(n,e){if(typeof e=="string"&&T(n,e))return n[e];if(!e)return n;if(typeof e!="string"){for(var a=[],o=0,l=e.length;o<l;++o){var u=H(n,e[o]);a.push(u)}return a}var h=e.indexOf(".");if(h!==-1){var v=n[e.substr(0,h)];return v==null?void 0:H(v,e.substr(h+1))}}function Z(n,e,a){if(n&&e!==void 0&&!("isFrozen"in Object&&Object.isFrozen(n)))if(typeof e!="string"&&"length"in e){q(typeof a!="string"&&"length"in a);for(var o=0,l=e.length;o<l;++o)Z(n,e[o],a[o])}else{var u,h,v=e.indexOf(".");v!==-1?(u=e.substr(0,v),(h=e.substr(v+1))===""?a===void 0?m(n)&&!isNaN(parseInt(u))?n.splice(u,1):delete n[u]:n[u]=a:Z(v=!(v=n[u])||!T(n,u)?n[u]={}:v,h,a)):a===void 0?m(n)&&!isNaN(parseInt(e))?n.splice(e,1):delete n[e]:n[e]=a}}function Pn(n){var e,a={};for(e in n)T(n,e)&&(a[e]=n[e]);return a}var Wn=[].concat;function Dn(n){return Wn.apply([],n)}var ft="BigUint64Array,BigInt64Array,Array,Boolean,String,Date,RegExp,Blob,File,FileList,FileSystemFileHandle,FileSystemDirectoryHandle,ArrayBuffer,DataView,Uint8ClampedArray,ImageBitmap,ImageData,Map,Set,CryptoKey".split(",").concat(Dn([8,16,32,64].map(function(n){return["Int","Uint","Float"].map(function(e){return e+n+"Array"})}))).filter(function(n){return c[n]}),Cn=new Set(ft.map(function(n){return c[n]})),zn=null;function Un(n){return zn=new WeakMap,n=(function e(a){if(!a||typeof a!="object")return a;var o=zn.get(a);if(o)return o;if(m(a)){o=[],zn.set(a,o);for(var l=0,u=a.length;l<u;++l)o.push(e(a[l]))}else if(Cn.has(a.constructor))o=a;else{var h,v=w(a);for(h in o=v===Object.prototype?{}:Object.create(v),zn.set(a,o),a)T(a,h)&&(o[h]=e(a[h]))}return o})(n),zn=null,n}var bn={}.toString;function sn(n){return bn.call(n).slice(8,-1)}var te=typeof Symbol<"u"?Symbol.iterator:"@@iterator",Rn=typeof te=="symbol"?function(n){var e;return n!=null&&(e=n[te])&&e.apply(n)}:function(){return null};function En(n,e){return e=n.indexOf(e),0<=e&&n.splice(e,1),0<=e}var nn={};function Nn(n){var e,a,o,l;if(arguments.length===1){if(m(n))return n.slice();if(this===nn&&typeof n=="string")return[n];if(l=Rn(n)){for(a=[];!(o=l.next()).done;)a.push(o.value);return a}if(n==null)return[n];if(typeof(e=n.length)!="number")return[n];for(a=new Array(e);e--;)a[e]=n[e];return a}for(e=arguments.length,a=new Array(e);e--;)a[e]=arguments[e];return a}var Xn=typeof Symbol<"u"?function(n){return n[Symbol.toStringTag]==="AsyncFunction"}:function(){return!1},Ot=["Unknown","Constraint","Data","TransactionInactive","ReadOnly","Version","NotFound","InvalidState","InvalidAccess","Abort","Timeout","QuotaExceeded","Syntax","DataClone"],We=["Modify","Bulk","OpenFailed","VersionChange","Schema","Upgrade","InvalidTable","MissingAPI","NoSuchDatabase","InvalidArgument","SubTransaction","Unsupported","Internal","DatabaseClosed","PrematureCommit","ForeignAwait"].concat(Ot),Q={VersionChanged:"Database version changed by other database connection",DatabaseClosed:"Database has been closed",Abort:"Transaction aborted",TransactionInactive:"Transaction has already completed or failed",MissingAPI:"IndexedDB API missing. Please visit https://tinyurl.com/y2uuvskb"};function an(n,e){this.name=n,this.message=e}function xn(n,e){return n+". Errors: "+Object.keys(e).map(function(a){return e[a].toString()}).filter(function(a,o,l){return l.indexOf(a)===o}).join(`
`)}function Sn(n,e,a,o){this.failures=e,this.failedKeys=o,this.successCount=a,this.message=xn(n,e)}function Vn(n,e){this.name="BulkError",this.failures=Object.keys(e).map(function(a){return e[a]}),this.failuresByPos=e,this.message=xn(n,this.failures)}$(an).from(Error).extend({toString:function(){return this.name+": "+this.message}}),$(Sn).from(an),$(Vn).from(an);var ve=We.reduce(function(n,e){return n[e]=e+"Error",n},{}),Ye=an,tn=We.reduce(function(n,e){var a=e+"Error";function o(l,u){this.name=a,l?typeof l=="string"?(this.message="".concat(l).concat(u?`
 `+u:""),this.inner=u||null):typeof l=="object"&&(this.message="".concat(l.name," ").concat(l.message),this.inner=l):(this.message=Q[e]||a,this.inner=null)}return $(o).from(Ye),n[e]=o,n},{});tn.Syntax=SyntaxError,tn.Type=TypeError,tn.Range=RangeError;var ai=Ot.reduce(function(n,e){return n[e+"Error"]=tn[e],n},{}),ar=We.reduce(function(n,e){return["Syntax","Type","Range"].indexOf(e)===-1&&(n[e+"Error"]=tn[e]),n},{});function On(){}function Tt(n){return n}function Ki(n,e){return n==null||n===Tt?e:function(a){return e(n(a))}}function lt(n,e){return function(){n.apply(this,arguments),e.apply(this,arguments)}}function qi(n,e){return n===On?e:function(){var a=n.apply(this,arguments);a!==void 0&&(arguments[0]=a);var o=this.onsuccess,l=this.onerror;this.onsuccess=null,this.onerror=null;var u=e.apply(this,arguments);return o&&(this.onsuccess=this.onsuccess?lt(o,this.onsuccess):o),l&&(this.onerror=this.onerror?lt(l,this.onerror):l),u!==void 0?u:a}}function Vi(n,e){return n===On?e:function(){n.apply(this,arguments);var a=this.onsuccess,o=this.onerror;this.onsuccess=this.onerror=null,e.apply(this,arguments),a&&(this.onsuccess=this.onsuccess?lt(a,this.onsuccess):a),o&&(this.onerror=this.onerror?lt(o,this.onerror):o)}}function Hi(n,e){return n===On?e:function(a){var o=n.apply(this,arguments);p(a,o);var l=this.onsuccess,u=this.onerror;return this.onsuccess=null,this.onerror=null,a=e.apply(this,arguments),l&&(this.onsuccess=this.onsuccess?lt(l,this.onsuccess):l),u&&(this.onerror=this.onerror?lt(u,this.onerror):u),o===void 0?a===void 0?void 0:a:p(o,a)}}function Gi(n,e){return n===On?e:function(){return e.apply(this,arguments)!==!1&&n.apply(this,arguments)}}function Ar(n,e){return n===On?e:function(){var a=n.apply(this,arguments);if(a&&typeof a.then=="function"){for(var o=this,l=arguments.length,u=new Array(l);l--;)u[l]=arguments[l];return a.then(function(){return e.apply(o,u)})}return e.apply(this,arguments)}}ar.ModifyError=Sn,ar.DexieError=an,ar.BulkError=Vn;var Fe=typeof location<"u"&&/^(http|https):\/\/(localhost|127\.0\.0\.1)/.test(location.href);function oi(n){Fe=n}var It={},si=100,ft=typeof Promise>"u"?[]:(function(){var n=Promise.resolve();if(typeof crypto>"u"||!crypto.subtle)return[n,w(n),n];var e=crypto.subtle.digest("SHA-512",new Uint8Array([0]));return[e,w(e),n]})(),Ot=ft[0],We=ft[1],ft=ft[2],We=We&&We.then,ut=Ot&&Ot.constructor,Cr=!!ft,At=function(n,e){li.push([n,e]),or&&(queueMicrotask(Hc),or=!1)},zr=!0,or=!0,sr=[],Yi=[],to=Tt,Ct={id:"global",global:!0,ref:0,unhandleds:[],onunhandled:On,pgp:!1,env:{},finalize:On},rn=Ct,li=[],lr=0,Wi=[];function X(n){if(typeof this!="object")throw new TypeError("Promises must be constructed via new");this._listeners=[],this._lib=!1;var e=this._PSD=rn;if(typeof n!="function"){if(n!==It)throw new TypeError("Not a function");return this._state=arguments[1],this._value=arguments[2],void(this._state===!1&&io(this,this._value))}this._state=null,this._value=null,++e.ref,(function a(o,l){try{l(function(u){if(o._state===null){if(u===o)throw new TypeError("A promise cannot be resolved with itself.");var h=o._lib&&Dr();u&&typeof u.then=="function"?a(o,function(v,g){u instanceof X?u._then(v,g):u.then(v,g)}):(o._state=!0,o._value=u,pl(o)),h&&Rr()}},io.bind(null,o))}catch(u){io(o,u)}})(this,n)}var ro={get:function(){var n=rn,e=Zi;function a(o,l){var u=this,h=!n.global&&(n!==rn||e!==Zi),v=h&&!Dt(),g=new X(function(_,k){ao(u,new vl(wl(o,n,h,v),wl(l,n,h,v),_,k,n))});return this._consoleTask&&(g._consoleTask=this._consoleTask),g}return a.prototype=It,a},set:function(n){C(this,"then",n&&n.prototype===It?ro:{get:function(){return n},set:ro.set})}};function vl(n,e,a,o,l){this.onFulfilled=typeof n=="function"?n:null,this.onRejected=typeof e=="function"?e:null,this.resolve=a,this.reject=o,this.psd=l}function io(n,e){var a,o;Yi.push(e),n._state===null&&(a=n._lib&&Dr(),e=to(e),n._state=!1,n._value=e,o=n,sr.some(function(l){return l._value===o._value})||sr.push(o),pl(n),a&&Rr())}function pl(n){var e=n._listeners;n._listeners=[];for(var a=0,o=e.length;a<o;++a)ao(n,e[a]);var l=n._PSD;--l.ref||l.finalize(),lr===0&&(++lr,At(function(){--lr==0&&oo()},[]))}function ao(n,e){if(n._state!==null){var a=n._state?e.onFulfilled:e.onRejected;if(a===null)return(n._state?e.resolve:e.reject)(n._value);++e.psd.ref,++lr,At(Vc,[a,n,e])}else n._listeners.push(e)}function Vc(n,e,a){try{var o,l=e._value;!e._state&&Yi.length&&(Yi=[]),o=Fe&&e._consoleTask?e._consoleTask.run(function(){return n(l)}):n(l),e._state||Yi.indexOf(l)!==-1||(function(u){for(var h=sr.length;h;)if(sr[--h]._value===u._value)return sr.splice(h,1)})(e),a.resolve(o)}catch(u){a.reject(u)}finally{--lr==0&&oo(),--a.psd.ref||a.psd.finalize()}}function Hc(){fr(Ct,function(){Dr()&&Rr()})}function Dr(){var n=zr;return or=zr=!1,n}function Rr(){var n,e,a;do for(;0<li.length;)for(n=li,li=[],a=n.length,e=0;e<a;++e){var o=n[e];o[0].apply(null,o[1])}while(0<li.length);or=zr=!0}function oo(){var n=sr;sr=[],n.forEach(function(o){o._PSD.onunhandled.call(null,o._value,o)});for(var e=Wi.slice(0),a=e.length;a;)e[--a]()}function Xi(n){return new X(It,!1,n)}function Hn(n,e){var a=rn;return function(){var o=Dr(),l=rn;try{return Rt(a,!0),n.apply(this,arguments)}catch(u){e&&e(u)}finally{Rt(l,!1),o&&Rr()}}}j(X.prototype,{then:ro,_then:function(n,e){ao(this,new vl(null,null,n,e,rn))},catch:function(n){if(arguments.length===1)return this.then(null,n);var e=n,a=arguments[1];return typeof e=="function"?this.then(null,function(o){return(o instanceof e?a:Xi)(o)}):this.then(null,function(o){return(o&&o.name===e?a:Xi)(o)})},finally:function(n){return this.then(function(e){return X.resolve(n()).then(function(){return e})},function(e){return X.resolve(n()).then(function(){return Xi(e)})})},timeout:function(n,e){var a=this;return n<1/0?new X(function(o,l){var u=setTimeout(function(){return l(new tn.Timeout(e))},n);a.then(o,l).finally(clearTimeout.bind(null,u))}):this}}),typeof Symbol<"u"&&Symbol.toStringTag&&C(X.prototype,Symbol.toStringTag,"Dexie.Promise"),Ct.env=yl(),j(X,{all:function(){var n=Nn.apply(null,arguments).map(na);return new X(function(e,a){n.length===0&&e([]);var o=n.length;n.forEach(function(l,u){return X.resolve(l).then(function(h){n[u]=h,--o||e(n)},a)})})},resolve:function(n){return n instanceof X?n:n&&typeof n.then=="function"?new X(function(e,a){n.then(e,a)}):new X(It,!0,n)},reject:Xi,race:function(){var n=Nn.apply(null,arguments).map(na);return new X(function(e,a){n.map(function(o){return X.resolve(o).then(e,a)})})},PSD:{get:function(){return rn},set:function(n){return rn=n}},totalEchoes:{get:function(){return Zi}},newPSD:zt,usePSD:fr,scheduler:{get:function(){return At},set:function(n){At=n}},rejectionMapper:{get:function(){return to},set:function(n){to=n}},follow:function(n,e){return new X(function(a,o){return zt(function(l,u){var h=rn;h.unhandleds=[],h.onunhandled=u,h.finalize=lt(function(){var v,g=this;v=function(){g.unhandleds.length===0?l():u(g.unhandleds[0])},Wi.push(function _(){v(),Wi.splice(Wi.indexOf(_),1)}),++lr,At(function(){--lr==0&&oo()},[])},h.finalize),n()},e,a,o)})}}),ut&&(ut.allSettled&&C(X,"allSettled",function(){var n=Nn.apply(null,arguments).map(na);return new X(function(e){n.length===0&&e([]);var a=n.length,o=new Array(a);n.forEach(function(l,u){return X.resolve(l).then(function(h){return o[u]={status:"fulfilled",value:h}},function(h){return o[u]={status:"rejected",reason:h}}).then(function(){return--a||e(o)})})})}),ut.any&&typeof AggregateError<"u"&&C(X,"any",function(){var n=Nn.apply(null,arguments).map(na);return new X(function(e,a){n.length===0&&a(new AggregateError([]));var o=n.length,l=new Array(o);n.forEach(function(u,h){return X.resolve(u).then(function(v){return e(v)},function(v){l[h]=v,--o||a(new AggregateError(l))})})})}),ut.withResolvers&&(X.withResolvers=ut.withResolvers));var fe={awaits:0,echoes:0,id:0},Gc=0,Ji=[],Qi=0,Zi=0,Yc=0;function zt(n,e,a,o){var l=rn,u=Object.create(l);return u.parent=l,u.ref=0,u.global=!1,u.id=++Yc,Ct.env,u.env=Cr?{Promise:X,PromiseProp:{value:X,configurable:!0,writable:!0},all:X.all,race:X.race,allSettled:X.allSettled,any:X.any,resolve:X.resolve,reject:X.reject}:{},e&&p(u,e),++l.ref,u.finalize=function(){--this.parent.ref||this.parent.finalize()},o=fr(u,n,a,o),u.ref===0&&u.finalize(),o}function $r(){return fe.id||(fe.id=++Gc),++fe.awaits,fe.echoes+=si,fe.id}function Dt(){return!!fe.awaits&&(--fe.awaits==0&&(fe.id=0),fe.echoes=fe.awaits*si,!0)}function na(n){return fe.echoes&&n&&n.constructor===ut?($r(),n.then(function(e){return Dt(),e},function(e){return Dt(),re(e)})):n}function Wc(){var n=Ji[Ji.length-1];Ji.pop(),Rt(n,!1)}function Rt(n,e){var a,o=rn;(e?!fe.echoes||Qi++&&n===rn:!Qi||--Qi&&n===rn)||queueMicrotask(e?(function(l){++Zi,fe.echoes&&--fe.echoes!=0||(fe.echoes=fe.awaits=fe.id=0),Ji.push(rn),Rt(l,!0)}).bind(null,n):Wc),n!==rn&&(rn=n,o===Ct&&(Ct.env=yl()),Cr&&(a=Ct.env.Promise,e=n.env,(o.global||n.global)&&(Object.defineProperty(c,"Promise",e.PromiseProp),a.all=e.all,a.race=e.race,a.resolve=e.resolve,a.reject=e.reject,e.allSettled&&(a.allSettled=e.allSettled),e.any&&(a.any=e.any))))}function yl(){var n=c.Promise;return Cr?{Promise:n,PromiseProp:Object.getOwnPropertyDescriptor(c,"Promise"),all:n.all,race:n.race,allSettled:n.allSettled,any:n.any,resolve:n.resolve,reject:n.reject}:{}}function fr(n,e,a,o,l){var u=rn;try{return Rt(n,!0),e(a,o,l)}finally{Rt(u,!1)}}function wl(n,e,a,o){return typeof n!="function"?n:function(){var l=rn;a&&$r(),Rt(e,!0);try{return n.apply(this,arguments)}finally{Rt(l,!1),o&&queueMicrotask(Dt)}}}function so(n){Promise===ut&&fe.echoes===0?Qi===0?n():enqueueNativeMicroTask(n):setTimeout(n,0)}(""+We).indexOf("[native code]")===-1&&($r=Dt=On);var re=X.reject,ur="￿",yt="Invalid key provided. Keys must be of type string, number, Date or Array<string | number | Date>.",gl="String expected.",Mr=[],ea="__dbnames",lo="readonly",fo="readwrite";function cr(n,e){return n?e?function(){return n.apply(this,arguments)&&e.apply(this,arguments)}:n:e}var bl={type:3,lower:-1/0,lowerOpen:!1,upper:[[]],upperOpen:!1};function ta(n){return typeof n!="string"||/\./.test(n)?function(e){return e}:function(e){return e[n]===void 0&&n in e&&delete(e=Un(e))[n],e}}function _l(){throw tn.Type("Entity instances must never be new:ed. Instances are generated by the framework bypassing the constructor.")}function jn(n,e){try{var a=xl(n),o=xl(e);if(a!==o)return a==="Array"?1:o==="Array"?-1:a==="binary"?1:o==="binary"?-1:a==="string"?1:o==="string"?-1:a==="Date"?1:o!=="Date"?NaN:-1;switch(a){case"number":case"Date":case"string":return e<n?1:n<e?-1:0;case"binary":return(function(l,u){for(var h=l.length,v=u.length,g=h<v?h:v,_=0;_<g;++_)if(l[_]!==u[_])return l[_]<u[_]?-1:1;return h===v?0:h<v?-1:1})(kl(n),kl(e));case"Array":return(function(l,u){for(var h=l.length,v=u.length,g=h<v?h:v,_=0;_<g;++_){var k=jn(l[_],u[_]);if(k!==0)return k}return h===v?0:h<v?-1:1})(n,e)}}catch{}return NaN}function xl(n){var e=typeof n;return e!="object"?e:ArrayBuffer.isView(n)?"binary":(n=sn(n),n==="ArrayBuffer"?"binary":n)}function kl(n){return n instanceof Uint8Array?n:ArrayBuffer.isView(n)?new Uint8Array(n.buffer,n.byteOffset,n.byteLength):new Uint8Array(n)}function ra(n,e,a){var o=n.schema.yProps;return o?(e&&0<a.numFailures&&(e=e.filter(function(l,u){return!a.failures[u]})),Promise.all(o.map(function(l){return l=l.updatesTable,e?n.db.table(l).where("k").anyOf(e).delete():n.db.table(l).clear()})).then(function(){return a})):a}var Pl=(Ln.prototype._trans=function(n,e,a){var o=this._tx||rn.trans,l=this.name,u=Fe&&typeof console<"u"&&console.createTask&&console.createTask("Dexie: ".concat(n==="readonly"?"read":"write"," ").concat(this.name));function h(_,k,y){if(!y.schema[l])throw new tn.NotFound("Table "+l+" not part of transaction");return e(y.idbtrans,y)}var v=Dr();try{var g=o&&o.db._novip===this.db._novip?o===rn.trans?o._promise(n,h,a):zt(function(){return o._promise(n,h,a)},{trans:o,transless:rn.transless||rn}):(function _(k,y,E,b){if(k.idbdb&&(k._state.openComplete||rn.letThrough||k._vip)){var x=k._createTransaction(y,E,k._dbSchema);try{x.create(),k._state.PR1398_maxLoop=3}catch(U){return U.name===ve.InvalidState&&k.isOpen()&&0<--k._state.PR1398_maxLoop?(console.warn("Dexie: Need to reopen db"),k.close({disableAutoOpen:!1}),k.open().then(function(){return _(k,y,E,b)})):re(U)}return x._promise(y,function(U,P){return zt(function(){return rn.trans=x,b(U,P,x)})}).then(function(U){if(y==="readwrite")try{x.idbtrans.commit()}catch{}return y==="readonly"?U:x._completion.then(function(){return U})})}if(k._state.openComplete)return re(new tn.DatabaseClosed(k._state.dbOpenError));if(!k._state.isBeingOpened){if(!k._state.autoOpen)return re(new tn.DatabaseClosed);k.open().catch(On)}return k._state.dbReadyPromise.then(function(){return _(k,y,E,b)})})(this.db,n,[this.name],h);return u&&(g._consoleTask=u,g=g.catch(function(_){return console.trace(_),re(_)})),g}finally{v&&Rr()}},Ln.prototype.get=function(n,e){var a=this;return n&&n.constructor===Object?this.where(n).first(e):n==null?re(new tn.Type("Invalid argument to Table.get()")):this._trans("readonly",function(o){return a.core.get({trans:o,key:n}).then(function(l){return a.hook.reading.fire(l)})}).then(e)},Ln.prototype.where=function(n){if(typeof n=="string")return new this.db.WhereClause(this,n);if(m(n))return new this.db.WhereClause(this,"[".concat(n.join("+"),"]"));var e=d(n);if(e.length===1)return this.where(e[0]).equals(n[e[0]]);var a=this.schema.indexes.concat(this.schema.primKey).filter(function(v){if(v.compound&&e.every(function(_){return 0<=v.keyPath.indexOf(_)})){for(var g=0;g<e.length;++g)if(e.indexOf(v.keyPath[g])===-1)return!1;return!0}return!1}).sort(function(v,g){return v.keyPath.length-g.keyPath.length})[0];if(a&&this.db._maxKey!==ur){var u=a.keyPath.slice(0,e.length);return this.where(u).equals(u.map(function(g){return n[g]}))}!a&&Fe&&console.warn("The query ".concat(JSON.stringify(n)," on ").concat(this.name," would benefit from a ")+"compound index [".concat(e.join("+"),"]"));var o=this.schema.idxByName;function l(v,g){return jn(v,g)===0}var h=e.reduce(function(y,g){var _=y[0],k=y[1],y=o[g],E=n[g];return[_||y,_||!y?cr(k,y&&y.multi?function(b){return b=H(b,g),m(b)&&b.some(function(x){return l(E,x)})}:function(b){return l(E,H(b,g))}):k]},[null,null]),u=h[0],h=h[1];return u?this.where(u.name).equals(n[u.keyPath]).filter(h):a?this.filter(h):this.where(e).equals("")},Ln.prototype.filter=function(n){return this.toCollection().and(n)},Ln.prototype.count=function(n){return this.toCollection().count(n)},Ln.prototype.offset=function(n){return this.toCollection().offset(n)},Ln.prototype.limit=function(n){return this.toCollection().limit(n)},Ln.prototype.each=function(n){return this.toCollection().each(n)},Ln.prototype.toArray=function(n){return this.toCollection().toArray(n)},Ln.prototype.toCollection=function(){return new this.db.Collection(new this.db.WhereClause(this))},Ln.prototype.orderBy=function(n){return new this.db.Collection(new this.db.WhereClause(this,m(n)?"[".concat(n.join("+"),"]"):n))},Ln.prototype.reverse=function(){return this.toCollection().reverse()},Ln.prototype.mapToClass=function(n){var e,a=this.db,o=this.name;function l(){return e!==null&&e.apply(this,arguments)||this}(this.schema.mappedClass=n).prototype instanceof _l&&((function(g,_){if(typeof _!="function"&&_!==null)throw new TypeError("Class extends value "+String(_)+" is not a constructor or null");function k(){this.constructor=g}i(g,_),g.prototype=_===null?Object.create(_):(k.prototype=_.prototype,new k)})(l,e=n),Object.defineProperty(l.prototype,"db",{get:function(){return a},enumerable:!1,configurable:!0}),l.prototype.table=function(){return o},n=l);for(var u=new Set,h=n.prototype;h;h=w(h))Object.getOwnPropertyNames(h).forEach(function(g){return u.add(g)});function v(g){if(!g)return g;var _,k=Object.create(n.prototype);for(_ in g)if(!u.has(_))try{k[_]=g[_]}catch{}return k}return this.schema.readHook&&this.hook.reading.unsubscribe(this.schema.readHook),this.schema.readHook=v,this.hook("reading",v),n},Ln.prototype.defineClass=function(){return this.mapToClass(function(n){p(this,n)})},Ln.prototype.add=function(n,e){var a=this,o=this.schema.primKey,l=o.auto,u=o.keyPath,h=n;return u&&l&&(h=ta(u)(n)),this._trans("readwrite",function(v){return a.core.mutate({trans:v,type:"add",keys:e!=null?[e]:null,values:[h]})}).then(function(v){return v.numFailures?X.reject(v.failures[0]):v.lastResult}).then(function(v){if(u)try{Z(n,u,v)}catch{}return v})},Ln.prototype.update=function(n,e){return typeof n!="object"||m(n)?this.where(":id").equals(n).modify(e):(n=H(n,this.schema.primKey.keyPath),n===void 0?re(new tn.InvalidArgument("Given object does not contain its primary key")):this.where(":id").equals(n).modify(e))},Ln.prototype.put=function(n,e){var a=this,o=this.schema.primKey,l=o.auto,u=o.keyPath,h=n;return u&&l&&(h=ta(u)(n)),this._trans("readwrite",function(v){return a.core.mutate({trans:v,type:"put",values:[h],keys:e!=null?[e]:null})}).then(function(v){return v.numFailures?X.reject(v.failures[0]):v.lastResult}).then(function(v){if(u)try{Z(n,u,v)}catch{}return v})},Ln.prototype.delete=function(n){var e=this;return this._trans("readwrite",function(a){return e.core.mutate({trans:a,type:"delete",keys:[n]}).then(function(o){return ra(e,[n],o)}).then(function(o){return o.numFailures?X.reject(o.failures[0]):void 0})})},Ln.prototype.clear=function(){var n=this;return this._trans("readwrite",function(e){return n.core.mutate({trans:e,type:"deleteRange",range:bl}).then(function(a){return ra(n,null,a)})}).then(function(e){return e.numFailures?X.reject(e.failures[0]):void 0})},Ln.prototype.bulkGet=function(n){var e=this;return this._trans("readonly",function(a){return e.core.getMany({keys:n,trans:a}).then(function(o){return o.map(function(l){return e.hook.reading.fire(l)})})})},Ln.prototype.bulkAdd=function(n,e,a){var o=this,l=Array.isArray(e)?e:void 0,u=(a=a||(l?void 0:e))?a.allKeys:void 0;return this._trans("readwrite",function(h){var _=o.schema.primKey,v=_.auto,_=_.keyPath;if(_&&l)throw new tn.InvalidArgument("bulkAdd(): keys argument invalid on tables with inbound keys");if(l&&l.length!==n.length)throw new tn.InvalidArgument("Arguments objects and keys must have the same length");var g=n.length,_=_&&v?n.map(ta(_)):n;return o.core.mutate({trans:h,type:"add",keys:l,values:_,wantResults:u}).then(function(x){var y=x.numFailures,E=x.results,b=x.lastResult,x=x.failures;if(y===0)return u?E:b;throw new Vn("".concat(o.name,".bulkAdd(): ").concat(y," of ").concat(g," operations failed"),x)})})},Ln.prototype.bulkPut=function(n,e,a){var o=this,l=Array.isArray(e)?e:void 0,u=(a=a||(l?void 0:e))?a.allKeys:void 0;return this._trans("readwrite",function(h){var _=o.schema.primKey,v=_.auto,_=_.keyPath;if(_&&l)throw new tn.InvalidArgument("bulkPut(): keys argument invalid on tables with inbound keys");if(l&&l.length!==n.length)throw new tn.InvalidArgument("Arguments objects and keys must have the same length");var g=n.length,_=_&&v?n.map(ta(_)):n;return o.core.mutate({trans:h,type:"put",keys:l,values:_,wantResults:u}).then(function(x){var y=x.numFailures,E=x.results,b=x.lastResult,x=x.failures;if(y===0)return u?E:b;throw new Vn("".concat(o.name,".bulkPut(): ").concat(y," of ").concat(g," operations failed"),x)})})},Ln.prototype.bulkUpdate=function(n){var e=this,a=this.core,o=n.map(function(h){return h.key}),l=n.map(function(h){return h.changes}),u=[];return this._trans("readwrite",function(h){return a.getMany({trans:h,keys:o,cache:"clone"}).then(function(v){var g=[],_=[];n.forEach(function(y,E){var b=y.key,x=y.changes,U=v[E];if(U){for(var P=0,S=Object.keys(x);P<S.length;P++){var O=S[P],A=x[O];if(O===e.schema.primKey.keyPath){if(jn(A,b)!==0)throw new tn.Constraint("Cannot update primary key in bulkUpdate()")}else Z(U,O,A)}u.push(E),g.push(b),_.push(U)}});var k=g.length;return a.mutate({trans:h,type:"put",keys:g,values:_,updates:{keys:o,changeSpecs:l}}).then(function(y){var E=y.numFailures,b=y.failures;if(E===0)return k;for(var x=0,U=Object.keys(b);x<U.length;x++){var P,S=U[x],O=u[Number(S)];O!=null&&(P=b[S],delete b[S],b[O]=P)}throw new Vn("".concat(e.name,".bulkUpdate(): ").concat(E," of ").concat(k," operations failed"),b)})})})},Ln.prototype.bulkDelete=function(n){var e=this,a=n.length;return this._trans("readwrite",function(o){return e.core.mutate({trans:o,type:"delete",keys:n}).then(function(l){return ra(e,n,l)})}).then(function(h){var l=h.numFailures,u=h.lastResult,h=h.failures;if(l===0)return u;throw new Vn("".concat(e.name,".bulkDelete(): ").concat(l," of ").concat(a," operations failed"),h)})},Ln);function Ln(){}function fi(n){function e(h,v){if(v){for(var g=arguments.length,_=new Array(g-1);--g;)_[g-1]=arguments[g];return a[h].subscribe.apply(null,_),n}if(typeof h=="string")return a[h]}var a={};e.addEventType=u;for(var o=1,l=arguments.length;o<l;++o)u(arguments[o]);return e;function u(h,v,g){if(typeof h!="object"){var _;v=v||Gi;var k={subscribers:[],fire:g=g||On,subscribe:function(y){k.subscribers.indexOf(y)===-1&&(k.subscribers.push(y),k.fire=v(k.fire,y))},unsubscribe:function(y){k.subscribers=k.subscribers.filter(function(E){return E!==y}),k.fire=k.subscribers.reduce(v,g)}};return a[h]=e[h]=k}d(_=h).forEach(function(y){var E=_[y];if(m(E))u(y,_[y][0],_[y][1]);else{if(E!=="asap")throw new tn.InvalidArgument("Invalid event config");var b=u(y,Tt,function(){for(var x=arguments.length,U=new Array(x);x--;)U[x]=arguments[x];b.subscribers.forEach(function(P){ln(function(){P.apply(null,U)})})})}})}}function ui(n,e){return $(e).from({prototype:n}),e}function Fr(n,e){return!(n.filter||n.algorithm||n.or)&&(e?n.justLimit:!n.replayFilter)}function uo(n,e){n.filter=cr(n.filter,e)}function co(n,e,a){var o=n.replayFilter;n.replayFilter=o?function(){return cr(o(),e())}:e,n.justLimit=a&&!o}function ia(n,e){if(n.isPrimKey)return e.primaryKey;var a=e.getIndexByKeyPath(n.index);if(!a)throw new tn.Schema("KeyPath "+n.index+" on object store "+e.name+" is not indexed");return a}function Ul(n,e,a){var o=ia(n,e.schema);return e.openCursor({trans:a,values:!n.keysOnly,reverse:n.dir==="prev",unique:!!n.unique,query:{index:o,range:n.range}})}function aa(n,e,a,o){var l=n.replayFilter?cr(n.filter,n.replayFilter()):n.filter;if(n.or){var u={},h=function(v,g,_){var k,y;l&&!l(g,_,function(E){return g.stop(E)},function(E){return g.fail(E)})||((y=""+(k=g.primaryKey))=="[object ArrayBuffer]"&&(y=""+new Uint8Array(k)),T(u,y)||(u[y]=!0,e(v,g,_)))};return Promise.all([n.or._iterate(h,a),El(Ul(n,o,a),n.algorithm,h,!n.keysOnly&&n.valueMapper)])}return El(Ul(n,o,a),cr(n.algorithm,l),e,!n.keysOnly&&n.valueMapper)}function El(n,e,a,o){var l=Hn(o?function(u,h,v){return a(o(u),h,v)}:a);return n.then(function(u){if(u)return u.start(function(){var h=function(){return u.continue()};e&&!e(u,function(v){return h=v},function(v){u.stop(v),h=On},function(v){u.fail(v),h=On})||l(u.value,u,function(v){return h=v}),h()})})}var ci=(Sl.prototype.execute=function(n){var e=this["@@propmod"];if(e.add!==void 0){var a=e.add;if(m(a))return f(f([],m(n)?n:[],!0),a).sort();if(typeof a=="number")return(Number(n)||0)+a;if(typeof a=="bigint")try{return BigInt(n)+a}catch{return BigInt(0)+a}throw new TypeError("Invalid term ".concat(a))}if(e.remove!==void 0){var o=e.remove;if(m(o))return m(n)?n.filter(function(l){return!o.includes(l)}).sort():[];if(typeof o=="number")return Number(n)-o;if(typeof o=="bigint")try{return BigInt(n)-o}catch{return BigInt(0)-o}throw new TypeError("Invalid subtrahend ".concat(o))}return a=(a=e.replacePrefix)===null||a===void 0?void 0:a[0],a&&typeof n=="string"&&n.startsWith(a)?e.replacePrefix[1]+n.substring(a.length):n},Sl);function Sl(n){this["@@propmod"]=n}var Xc=(In.prototype._read=function(n,e){var a=this._ctx;return a.error?a.table._trans(null,re.bind(null,a.error)):a.table._trans("readonly",n).then(e)},In.prototype._write=function(n){var e=this._ctx;return e.error?e.table._trans(null,re.bind(null,e.error)):e.table._trans("readwrite",n,"locked")},In.prototype._addAlgorithm=function(n){var e=this._ctx;e.algorithm=cr(e.algorithm,n)},In.prototype._iterate=function(n,e){return aa(this._ctx,n,e,this._ctx.table.core)},In.prototype.clone=function(n){var e=Object.create(this.constructor.prototype),a=Object.create(this._ctx);return n&&p(a,n),e._ctx=a,e},In.prototype.raw=function(){return this._ctx.valueMapper=null,this},In.prototype.each=function(n){var e=this._ctx;return this._read(function(a){return aa(e,n,a,e.table.core)})},In.prototype.count=function(n){var e=this;return this._read(function(a){var o=e._ctx,l=o.table.core;if(Fr(o,!0))return l.count({trans:a,query:{index:ia(o,l.schema),range:o.range}}).then(function(h){return Math.min(h,o.limit)});var u=0;return aa(o,function(){return++u,!1},a,l).then(function(){return u})}).then(n)},In.prototype.sortBy=function(n,e){var a=n.split(".").reverse(),o=a[0],l=a.length-1;function u(g,_){return _?u(g[a[_]],_-1):g[o]}var h=this._ctx.dir==="next"?1:-1;function v(g,_){return jn(u(g,l),u(_,l))*h}return this.toArray(function(g){return g.sort(v)}).then(e)},In.prototype.toArray=function(n){var e=this;return this._read(function(a){var o=e._ctx;if(o.dir==="next"&&Fr(o,!0)&&0<o.limit){var l=o.valueMapper,u=ia(o,o.table.core.schema);return o.table.core.query({trans:a,limit:o.limit,values:!0,query:{index:u,range:o.range}}).then(function(v){return v=v.result,l?v.map(l):v})}var h=[];return aa(o,function(v){return h.push(v)},a,o.table.core).then(function(){return h})},n)},In.prototype.offset=function(n){var e=this._ctx;return n<=0||(e.offset+=n,Fr(e)?co(e,function(){var a=n;return function(o,l){return a===0||(a===1?--a:l(function(){o.advance(a),a=0}),!1)}}):co(e,function(){var a=n;return function(){return--a<0}})),this},In.prototype.limit=function(n){return this._ctx.limit=Math.min(this._ctx.limit,n),co(this._ctx,function(){var e=n;return function(a,o,l){return--e<=0&&o(l),0<=e}},!0),this},In.prototype.until=function(n,e){return uo(this._ctx,function(a,o,l){return!n(a.value)||(o(l),e)}),this},In.prototype.first=function(n){return this.limit(1).toArray(function(e){return e[0]}).then(n)},In.prototype.last=function(n){return this.reverse().first(n)},In.prototype.filter=function(n){var e;return uo(this._ctx,function(a){return n(a.value)}),(e=this._ctx).isMatch=cr(e.isMatch,n),this},In.prototype.and=function(n){return this.filter(n)},In.prototype.or=function(n){return new this.db.WhereClause(this._ctx.table,n,this)},In.prototype.reverse=function(){return this._ctx.dir=this._ctx.dir==="prev"?"next":"prev",this._ondirectionchange&&this._ondirectionchange(this._ctx.dir),this},In.prototype.desc=function(){return this.reverse()},In.prototype.eachKey=function(n){var e=this._ctx;return e.keysOnly=!e.isMatch,this.each(function(a,o){n(o.key,o)})},In.prototype.eachUniqueKey=function(n){return this._ctx.unique="unique",this.eachKey(n)},In.prototype.eachPrimaryKey=function(n){var e=this._ctx;return e.keysOnly=!e.isMatch,this.each(function(a,o){n(o.primaryKey,o)})},In.prototype.keys=function(n){var e=this._ctx;e.keysOnly=!e.isMatch;var a=[];return this.each(function(o,l){a.push(l.key)}).then(function(){return a}).then(n)},In.prototype.primaryKeys=function(n){var e=this._ctx;if(e.dir==="next"&&Fr(e,!0)&&0<e.limit)return this._read(function(o){var l=ia(e,e.table.core.schema);return e.table.core.query({trans:o,values:!1,limit:e.limit,query:{index:l,range:e.range}})}).then(function(o){return o.result}).then(n);e.keysOnly=!e.isMatch;var a=[];return this.each(function(o,l){a.push(l.primaryKey)}).then(function(){return a}).then(n)},In.prototype.uniqueKeys=function(n){return this._ctx.unique="unique",this.keys(n)},In.prototype.firstKey=function(n){return this.limit(1).keys(function(e){return e[0]}).then(n)},In.prototype.lastKey=function(n){return this.reverse().firstKey(n)},In.prototype.distinct=function(){var n=this._ctx,n=n.index&&n.table.schema.idxByName[n.index];if(!n||!n.multi)return this;var e={};return uo(this._ctx,function(l){var o=l.primaryKey.toString(),l=T(e,o);return e[o]=!0,!l}),this},In.prototype.modify=function(n){var e=this,a=this._ctx;return this._write(function(o){var l,u,h;h=typeof n=="function"?n:(l=d(n),u=l.length,function(S){for(var O=!1,A=0;A<u;++A){var z=l[A],B=n[z],K=H(S,z);B instanceof ci?(Z(S,z,B.execute(K)),O=!0):K!==B&&(Z(S,z,B),O=!0)}return O});var v=a.table.core,y=v.schema.primaryKey,g=y.outbound,_=y.extractKey,k=200,y=e.db._options.modifyChunkSize;y&&(k=typeof y=="object"?y[v.name]||y["*"]||200:y);function E(S,z){var A=z.failures,z=z.numFailures;x+=S-z;for(var B=0,K=d(A);B<K.length;B++){var N=K[B];b.push(A[N])}}var b=[],x=0,U=[],P=n===jl;return e.clone().primaryKeys().then(function(S){function O(z){var B=Math.min(k,S.length-z),K=S.slice(z,z+B);return(P?Promise.resolve([]):v.getMany({trans:o,keys:K,cache:"immutable"})).then(function(N){var L=[],Y=[],G=g?[]:null,J=P?K:[];if(!P)for(var yn=0;yn<B;++yn){var Tn=N[yn],dn={value:Un(Tn),primKey:S[z+yn]};h.call(dn,dn.value,dn)!==!1&&(dn.value==null?J.push(S[z+yn]):g||jn(_(Tn),_(dn.value))===0?(Y.push(dn.value),g&&G.push(S[z+yn])):(J.push(S[z+yn]),L.push(dn.value)))}return Promise.resolve(0<L.length&&v.mutate({trans:o,type:"add",values:L}).then(function(Jn){for(var fn in Jn.failures)J.splice(parseInt(fn),1);E(L.length,Jn)})).then(function(){return(0<Y.length||A&&typeof n=="object")&&v.mutate({trans:o,type:"put",keys:G,values:Y,criteria:A,changeSpec:typeof n!="function"&&n,isAdditionalChunk:0<z}).then(function(Jn){return E(Y.length,Jn)})}).then(function(){return(0<J.length||A&&P)&&v.mutate({trans:o,type:"delete",keys:J,criteria:A,isAdditionalChunk:0<z}).then(function(Jn){return ra(a.table,J,Jn)}).then(function(Jn){return E(J.length,Jn)})}).then(function(){return S.length>z+B&&O(z+k)})})}var A=Fr(a)&&a.limit===1/0&&(typeof n!="function"||P)&&{index:a.index,range:a.range};return O(0).then(function(){if(0<b.length)throw new Sn("Error modifying one or more objects",b,x,U);return S.length})})})},In.prototype.delete=function(){var n=this._ctx,e=n.range;return!Fr(n)||n.table.schema.yProps||!n.isPrimKey&&e.type!==3?this.modify(jl):this._write(function(a){var o=n.table.core.schema.primaryKey,l=e;return n.table.core.count({trans:a,query:{index:o,range:l}}).then(function(u){return n.table.core.mutate({trans:a,type:"deleteRange",range:l}).then(function(g){var v=g.failures,g=g.numFailures;if(g)throw new Sn("Could not delete some values",Object.keys(v).map(function(_){return v[_]}),u-g);return u-g})})})},In);function In(){}var jl=function(n,e){return e.value=null};function Jc(n,e){return n<e?-1:n===e?0:1}function Qc(n,e){return e<n?-1:n===e?0:1}function Be(n,e,a){return n=n instanceof Il?new n.Collection(n):n,n._ctx.error=new(a||TypeError)(e),n}function Br(n){return new n.Collection(n,function(){return Tl("")}).limit(0)}function oa(n,e,a,o){var l,u,h,v,g,_,k,y=a.length;if(!a.every(function(x){return typeof x=="string"}))return Be(n,gl);function E(x){l=x==="next"?function(P){return P.toUpperCase()}:function(P){return P.toLowerCase()},u=x==="next"?function(P){return P.toLowerCase()}:function(P){return P.toUpperCase()},h=x==="next"?Jc:Qc;var U=a.map(function(P){return{lower:u(P),upper:l(P)}}).sort(function(P,S){return h(P.lower,S.lower)});v=U.map(function(P){return P.upper}),g=U.map(function(P){return P.lower}),k=(_=x)==="next"?"":o}E("next"),n=new n.Collection(n,function(){return $t(v[0],g[y-1]+o)}),n._ondirectionchange=function(x){E(x)};var b=0;return n._addAlgorithm(function(x,U,P){var S=x.key;if(typeof S!="string")return!1;var O=u(S);if(e(O,g,b))return!0;for(var A=null,z=b;z<y;++z){var B=(function(K,N,L,Y,G,J){for(var yn=Math.min(K.length,Y.length),Tn=-1,dn=0;dn<yn;++dn){var Jn=N[dn];if(Jn!==Y[dn])return G(K[dn],L[dn])<0?K.substr(0,dn)+L[dn]+L.substr(dn+1):G(K[dn],Y[dn])<0?K.substr(0,dn)+Y[dn]+L.substr(dn+1):0<=Tn?K.substr(0,Tn)+N[Tn]+L.substr(Tn+1):null;G(K[dn],Jn)<0&&(Tn=dn)}return yn<Y.length&&J==="next"?K+L.substr(K.length):yn<K.length&&J==="prev"?K.substr(0,L.length):Tn<0?null:K.substr(0,Tn)+Y[Tn]+L.substr(Tn+1)})(S,O,v[z],g[z],h,_);B===null&&A===null?b=z+1:(A===null||0<h(A,B))&&(A=B)}return U(A!==null?function(){x.continue(A+k)}:P),!1}),n}function $t(n,e,a,o){return{type:2,lower:n,upper:e,lowerOpen:a,upperOpen:o}}function Tl(n){return{type:1,lower:n,upper:n}}var Il=(Object.defineProperty(ue.prototype,"Collection",{get:function(){return this._ctx.table.db.Collection},enumerable:!1,configurable:!0}),ue.prototype.between=function(n,e,a,o){a=a!==!1,o=o===!0;try{return 0<this._cmp(n,e)||this._cmp(n,e)===0&&(a||o)&&(!a||!o)?Br(this):new this.Collection(this,function(){return $t(n,e,!a,!o)})}catch{return Be(this,yt)}},ue.prototype.equals=function(n){return n==null?Be(this,yt):new this.Collection(this,function(){return Tl(n)})},ue.prototype.above=function(n){return n==null?Be(this,yt):new this.Collection(this,function(){return $t(n,void 0,!0)})},ue.prototype.aboveOrEqual=function(n){return n==null?Be(this,yt):new this.Collection(this,function(){return $t(n,void 0,!1)})},ue.prototype.below=function(n){return n==null?Be(this,yt):new this.Collection(this,function(){return $t(void 0,n,!1,!0)})},ue.prototype.belowOrEqual=function(n){return n==null?Be(this,yt):new this.Collection(this,function(){return $t(void 0,n)})},ue.prototype.startsWith=function(n){return typeof n!="string"?Be(this,gl):this.between(n,n+ur,!0,!0)},ue.prototype.startsWithIgnoreCase=function(n){return n===""?this.startsWith(n):oa(this,function(e,a){return e.indexOf(a[0])===0},[n],ur)},ue.prototype.equalsIgnoreCase=function(n){return oa(this,function(e,a){return e===a[0]},[n],"")},ue.prototype.anyOfIgnoreCase=function(){var n=Nn.apply(nn,arguments);return n.length===0?Br(this):oa(this,function(e,a){return a.indexOf(e)!==-1},n,"")},ue.prototype.startsWithAnyOfIgnoreCase=function(){var n=Nn.apply(nn,arguments);return n.length===0?Br(this):oa(this,function(e,a){return a.some(function(o){return e.indexOf(o)===0})},n,ur)},ue.prototype.anyOf=function(){var n=this,e=Nn.apply(nn,arguments),a=this._cmp;try{e.sort(a)}catch{return Be(this,yt)}if(e.length===0)return Br(this);var o=new this.Collection(this,function(){return $t(e[0],e[e.length-1])});o._ondirectionchange=function(u){a=u==="next"?n._ascending:n._descending,e.sort(a)};var l=0;return o._addAlgorithm(function(u,h,v){for(var g=u.key;0<a(g,e[l]);)if(++l===e.length)return h(v),!1;return a(g,e[l])===0||(h(function(){u.continue(e[l])}),!1)}),o},ue.prototype.notEqual=function(n){return this.inAnyRange([[-1/0,n],[n,this.db._maxKey]],{includeLowers:!1,includeUppers:!1})},ue.prototype.noneOf=function(){var n=Nn.apply(nn,arguments);if(n.length===0)return new this.Collection(this);try{n.sort(this._ascending)}catch{return Be(this,yt)}var e=n.reduce(function(a,o){return a?a.concat([[a[a.length-1][1],o]]):[[-1/0,o]]},null);return e.push([n[n.length-1],this.db._maxKey]),this.inAnyRange(e,{includeLowers:!1,includeUppers:!1})},ue.prototype.inAnyRange=function(S,e){var a=this,o=this._cmp,l=this._ascending,u=this._descending,h=this._min,v=this._max;if(S.length===0)return Br(this);if(!S.every(function(O){return O[0]!==void 0&&O[1]!==void 0&&l(O[0],O[1])<=0}))return Be(this,"First argument to inAnyRange() must be an Array of two-value Arrays [lower,upper] where upper must not be lower than lower",tn.InvalidArgument);var g=!e||e.includeLowers!==!1,_=e&&e.includeUppers===!0,k,y=l;function E(O,A){return y(O[0],A[0])}try{(k=S.reduce(function(O,A){for(var z=0,B=O.length;z<B;++z){var K=O[z];if(o(A[0],K[1])<0&&0<o(A[1],K[0])){K[0]=h(K[0],A[0]),K[1]=v(K[1],A[1]);break}}return z===B&&O.push(A),O},[])).sort(E)}catch{return Be(this,yt)}var b=0,x=_?function(O){return 0<l(O,k[b][1])}:function(O){return 0<=l(O,k[b][1])},U=g?function(O){return 0<u(O,k[b][0])}:function(O){return 0<=u(O,k[b][0])},P=x,S=new this.Collection(this,function(){return $t(k[0][0],k[k.length-1][1],!g,!_)});return S._ondirectionchange=function(O){y=O==="next"?(P=x,l):(P=U,u),k.sort(E)},S._addAlgorithm(function(O,A,z){for(var B,K=O.key;P(K);)if(++b===k.length)return A(z),!1;return!x(B=K)&&!U(B)||(a._cmp(K,k[b][1])===0||a._cmp(K,k[b][0])===0||A(function(){y===l?O.continue(k[b][0]):O.continue(k[b][1])}),!1)}),S},ue.prototype.startsWithAnyOf=function(){var n=Nn.apply(nn,arguments);return n.every(function(e){return typeof e=="string"})?n.length===0?Br(this):this.inAnyRange(n.map(function(e){return[e,e+ur]})):Be(this,"startsWithAnyOf() only works with strings")},ue);function ue(){}function ct(n){return Hn(function(e){return di(e),n(e.target.error),!1})}function di(n){n.stopPropagation&&n.stopPropagation(),n.preventDefault&&n.preventDefault()}var hi="storagemutated",ho="x-storagemutated-1",Mt=fi(null,hi),Zc=(dt.prototype._lock=function(){return q(!rn.global),++this._reculock,this._reculock!==1||rn.global||(rn.lockOwnerFor=this),this},dt.prototype._unlock=function(){if(q(!rn.global),--this._reculock==0)for(rn.global||(rn.lockOwnerFor=null);0<this._blockedFuncs.length&&!this._locked();){var n=this._blockedFuncs.shift();try{fr(n[1],n[0])}catch{}}return this},dt.prototype._locked=function(){return this._reculock&&rn.lockOwnerFor!==this},dt.prototype.create=function(n){var e=this;if(!this.mode)return this;var a=this.db.idbdb,o=this.db._state.dbOpenError;if(q(!this.idbtrans),!n&&!a)switch(o&&o.name){case"DatabaseClosedError":throw new tn.DatabaseClosed(o);case"MissingAPIError":throw new tn.MissingAPI(o.message,o);default:throw new tn.OpenFailed(o)}if(!this.active)throw new tn.TransactionInactive;return q(this._completion._state===null),(n=this.idbtrans=n||(this.db.core||a).transaction(this.storeNames,this.mode,{durability:this.chromeTransactionDurability})).onerror=Hn(function(l){di(l),e._reject(n.error)}),n.onabort=Hn(function(l){di(l),e.active&&e._reject(new tn.Abort(n.error)),e.active=!1,e.on("abort").fire(l)}),n.oncomplete=Hn(function(){e.active=!1,e._resolve(),"mutatedParts"in n&&Mt.storagemutated.fire(n.mutatedParts)}),this},dt.prototype._promise=function(n,e,a){var o=this;if(n==="readwrite"&&this.mode!=="readwrite")return re(new tn.ReadOnly("Transaction is readonly"));if(!this.active)return re(new tn.TransactionInactive);if(this._locked())return new X(function(u,h){o._blockedFuncs.push([function(){o._promise(n,e,a).then(u,h)},rn])});if(a)return zt(function(){var u=new X(function(h,v){o._lock();var g=e(h,v,o);g&&g.then&&g.then(h,v)});return u.finally(function(){return o._unlock()}),u._lib=!0,u});var l=new X(function(u,h){var v=e(u,h,o);v&&v.then&&v.then(u,h)});return l._lib=!0,l},dt.prototype._root=function(){return this.parent?this.parent._root():this},dt.prototype.waitFor=function(n){var e,a=this._root(),o=X.resolve(n);a._waitingFor?a._waitingFor=a._waitingFor.then(function(){return o}):(a._waitingFor=o,a._waitingQueue=[],e=a.idbtrans.objectStore(a.storeNames[0]),(function u(){for(++a._spinCount;a._waitingQueue.length;)a._waitingQueue.shift()();a._waitingFor&&(e.get(-1/0).onsuccess=u)})());var l=a._waitingFor;return new X(function(u,h){o.then(function(v){return a._waitingQueue.push(Hn(u.bind(null,v)))},function(v){return a._waitingQueue.push(Hn(h.bind(null,v)))}).finally(function(){a._waitingFor===l&&(a._waitingFor=null)})})},dt.prototype.abort=function(){this.active&&(this.active=!1,this.idbtrans&&this.idbtrans.abort(),this._reject(new tn.Abort))},dt.prototype.table=function(n){var e=this._memoizedTables||(this._memoizedTables={});if(T(e,n))return e[n];var a=this.schema[n];if(!a)throw new tn.NotFound("Table "+n+" not part of transaction");return a=new this.db.Table(n,a,this),a.core=this.db.core.table(n),e[n]=a},dt);function dt(){}function mo(n,e,a,o,l,u,h,v){return{name:n,keyPath:e,unique:a,multi:o,auto:l,compound:u,src:(a&&!h?"&":"")+(o?"*":"")+(l?"++":"")+Ol(e),type:v}}function Ol(n){return typeof n=="string"?n:n?"["+[].join.call(n,"+")+"]":""}function vo(n,e,a){return{name:n,primKey:e,indexes:a,mappedClass:null,idxByName:(o=function(l){return[l.name,l]},a.reduce(function(l,u,h){return h=o(u,h),h&&(l[h[0]]=h[1]),l},{}))};var o}var mi=function(n){try{return n.only([[]]),mi=function(){return[[]]},[[]]}catch{return mi=function(){return ur},ur}};function po(n){return n==null?function(){}:typeof n=="string"?(e=n).split(".").length===1?function(a){return a[e]}:function(a){return H(a,e)}:function(a){return H(a,n)};var e}function Al(n){return[].slice.call(n)}var nd=0;function vi(n){return n==null?":id":typeof n=="string"?n:"[".concat(n.join("+"),"]")}function ed(n,e,g){function o(P){if(P.type===3)return null;if(P.type===4)throw new Error("Cannot convert never type to IDBKeyRange");var b=P.lower,x=P.upper,U=P.lowerOpen,P=P.upperOpen;return b===void 0?x===void 0?null:e.upperBound(x,!!P):x===void 0?e.lowerBound(b,!!U):e.bound(b,x,!!U,!!P)}function l(E){var b,x=E.name;return{name:x,schema:E,mutate:function(U){var P=U.trans,S=U.type,O=U.keys,A=U.values,z=U.range;return new Promise(function(B,K){B=Hn(B);var N=P.objectStore(x),L=N.keyPath==null,Y=S==="put"||S==="add";if(!Y&&S!=="delete"&&S!=="deleteRange")throw new Error("Invalid operation type: "+S);var G,J=(O||A||{length:1}).length;if(O&&A&&O.length!==A.length)throw new Error("Given keys array must have same length as given values array.");if(J===0)return B({numFailures:0,failures:{},results:[],lastResult:void 0});function yn(Se){++Jn,di(Se)}var Tn=[],dn=[],Jn=0;if(S==="deleteRange"){if(z.type===4)return B({numFailures:Jn,failures:dn,results:[],lastResult:void 0});z.type===3?Tn.push(G=N.clear()):Tn.push(G=N.delete(o(z)))}else{var L=Y?L?[A,O]:[A,null]:[O,null],fn=L[0],_e=L[1];if(Y)for(var xe=0;xe<J;++xe)Tn.push(G=_e&&_e[xe]!==void 0?N[S](fn[xe],_e[xe]):N[S](fn[xe])),G.onerror=yn;else for(xe=0;xe<J;++xe)Tn.push(G=N[S](fn[xe])),G.onerror=yn}function wa(Se){Se=Se.target.result,Tn.forEach(function(mr,zo){return mr.error!=null&&(dn[zo]=mr.error)}),B({numFailures:Jn,failures:dn,results:S==="delete"?O:Tn.map(function(mr){return mr.result}),lastResult:Se})}G.onerror=function(Se){yn(Se),wa(Se)},G.onsuccess=wa})},getMany:function(U){var P=U.trans,S=U.keys;return new Promise(function(O,A){O=Hn(O);for(var z,B=P.objectStore(x),K=S.length,N=new Array(K),L=0,Y=0,G=function(Tn){Tn=Tn.target,N[Tn._pos]=Tn.result,++Y===L&&O(N)},J=ct(A),yn=0;yn<K;++yn)S[yn]!=null&&((z=B.get(S[yn]))._pos=yn,z.onsuccess=G,z.onerror=J,++L);L===0&&O(N)})},get:function(U){var P=U.trans,S=U.key;return new Promise(function(O,A){O=Hn(O);var z=P.objectStore(x).get(S);z.onsuccess=function(B){return O(B.target.result)},z.onerror=ct(A)})},query:(b=_,function(U){return new Promise(function(P,S){P=Hn(P);var O,A,z,L=U.trans,B=U.values,K=U.limit,G=U.query,N=K===1/0?void 0:K,Y=G.index,G=G.range,L=L.objectStore(x),Y=Y.isPrimaryKey?L:L.index(Y.name),G=o(G);if(K===0)return P({result:[]});b?((N=B?Y.getAll(G,N):Y.getAllKeys(G,N)).onsuccess=function(J){return P({result:J.target.result})},N.onerror=ct(S)):(O=0,A=!B&&"openKeyCursor"in Y?Y.openKeyCursor(G):Y.openCursor(G),z=[],A.onsuccess=function(J){var yn=A.result;return yn?(z.push(B?yn.value:yn.primaryKey),++O===K?P({result:z}):void yn.continue()):P({result:z})},A.onerror=ct(S))})}),openCursor:function(U){var P=U.trans,S=U.values,O=U.query,A=U.reverse,z=U.unique;return new Promise(function(B,K){B=Hn(B);var Y=O.index,N=O.range,L=P.objectStore(x),L=Y.isPrimaryKey?L:L.index(Y.name),Y=A?z?"prevunique":"prev":z?"nextunique":"next",G=!S&&"openKeyCursor"in L?L.openKeyCursor(o(N),Y):L.openCursor(o(N),Y);G.onerror=ct(K),G.onsuccess=Hn(function(J){var yn,Tn,dn,Jn,fn=G.result;fn?(fn.___id=++nd,fn.done=!1,yn=fn.continue.bind(fn),Tn=(Tn=fn.continuePrimaryKey)&&Tn.bind(fn),dn=fn.advance.bind(fn),Jn=function(){throw new Error("Cursor not stopped")},fn.trans=P,fn.stop=fn.continue=fn.continuePrimaryKey=fn.advance=function(){throw new Error("Cursor not started")},fn.fail=Hn(K),fn.next=function(){var _e=this,xe=1;return this.start(function(){return xe--?_e.continue():_e.stop()}).then(function(){return _e})},fn.start=function(_e){function xe(){if(G.result)try{_e()}catch(Se){fn.fail(Se)}else fn.done=!0,fn.start=function(){throw new Error("Cursor behind last entry")},fn.stop()}var wa=new Promise(function(Se,mr){Se=Hn(Se),G.onerror=ct(mr),fn.fail=mr,fn.stop=function(zo){fn.stop=fn.continue=fn.continuePrimaryKey=fn.advance=Jn,Se(zo)}});return G.onsuccess=Hn(function(Se){G.onsuccess=xe,xe()}),fn.continue=yn,fn.continuePrimaryKey=Tn,fn.advance=dn,xe(),wa},B(fn)):B(null)},K)})},count:function(U){var P=U.query,S=U.trans,O=P.index,A=P.range;return new Promise(function(z,B){var K=S.objectStore(x),N=O.isPrimaryKey?K:K.index(O.name),K=o(A),N=K?N.count(K):N.count();N.onsuccess=Hn(function(L){return z(L.target.result)}),N.onerror=ct(B)})}}}var u,h,v,k=(h=g,v=Al((u=n).objectStoreNames),{schema:{name:u.name,tables:v.map(function(E){return h.objectStore(E)}).map(function(E){var b=E.keyPath,P=E.autoIncrement,x=m(b),U={},P={name:E.name,primaryKey:{name:null,isPrimaryKey:!0,outbound:b==null,compound:x,keyPath:b,autoIncrement:P,unique:!0,extractKey:po(b)},indexes:Al(E.indexNames).map(function(S){return E.index(S)}).map(function(z){var O=z.name,A=z.unique,B=z.multiEntry,z=z.keyPath,B={name:O,compound:m(z),keyPath:z,unique:A,multiEntry:B,extractKey:po(z)};return U[vi(z)]=B}),getIndexByKeyPath:function(S){return U[vi(S)]}};return U[":id"]=P.primaryKey,b!=null&&(U[vi(b)]=P.primaryKey),P})},hasGetAll:0<v.length&&"getAll"in h.objectStore(v[0])&&!(typeof navigator<"u"&&/Safari/.test(navigator.userAgent)&&!/(Chrome\/|Edge\/)/.test(navigator.userAgent)&&[].concat(navigator.userAgent.match(/Safari\/(\d*)/))[1]<604)}),g=k.schema,_=k.hasGetAll,k=g.tables.map(l),y={};return k.forEach(function(E){return y[E.name]=E}),{stack:"dbcore",transaction:n.transaction.bind(n),table:function(E){if(!y[E])throw new Error("Table '".concat(E,"' not found"));return y[E]},MIN_KEY:-1/0,MAX_KEY:mi(e),schema:g}}function td(n,e,a,o){var l=a.IDBKeyRange;return a.indexedDB,{dbcore:(o=ed(e,l,o),n.dbcore.reduce(function(u,h){return h=h.create,s(s({},u),h(u))},o))}}function sa(n,o){var a=o.db,o=td(n._middlewares,a,n._deps,o);n.core=o.dbcore,n.tables.forEach(function(l){var u=l.name;n.core.schema.tables.some(function(h){return h.name===u})&&(l.core=n.core.table(u),n[u]instanceof n.Table&&(n[u].core=l.core))})}function la(n,e,a,o){a.forEach(function(l){var u=o[l];e.forEach(function(h){var v=(function g(_,k){return R(_,k)||(_=w(_))&&g(_,k)})(h,l);(!v||"value"in v&&v.value===void 0)&&(h===n.Transaction.prototype||h instanceof n.Transaction?C(h,l,{get:function(){return this.table(l)},set:function(g){D(this,l,{value:g,writable:!0,configurable:!0,enumerable:!0})}}):h[l]=new n.Table(l,u))})})}function yo(n,e){e.forEach(function(a){for(var o in a)a[o]instanceof n.Table&&delete a[o]})}function rd(n,e){return n._cfg.version-e._cfg.version}function id(n,e,a,o){var l=n._dbSchema;a.objectStoreNames.contains("$meta")&&!l.$meta&&(l.$meta=vo("$meta",zl("")[0],[]),n._storeNames.push("$meta"));var u=n._createTransaction("readwrite",n._storeNames,l);u.create(a),u._completion.catch(o);var h=u._reject.bind(u),v=rn.transless||rn;zt(function(){return rn.trans=u,rn.transless=v,e!==0?(sa(n,a),_=e,((g=u).storeNames.includes("$meta")?g.table("$meta").get("version").then(function(k){return k??_}):X.resolve(_)).then(function(k){return E=k,b=u,x=a,U=[],k=(y=n)._versions,P=y._dbSchema=ua(0,y.idbdb,x),(k=k.filter(function(S){return S._cfg.version>=E})).length!==0?(k.forEach(function(S){U.push(function(){var O=P,A=S._cfg.dbschema;ca(y,O,x),ca(y,A,x),P=y._dbSchema=A;var z=wo(O,A);z.add.forEach(function(Y){go(x,Y[0],Y[1].primKey,Y[1].indexes)}),z.change.forEach(function(Y){if(Y.recreate)throw new tn.Upgrade("Not yet support for changing primary key");var G=x.objectStore(Y.name);Y.add.forEach(function(J){return fa(G,J)}),Y.change.forEach(function(J){G.deleteIndex(J.name),fa(G,J)}),Y.del.forEach(function(J){return G.deleteIndex(J)})});var B=S._cfg.contentUpgrade;if(B&&S._cfg.version>E){sa(y,x),b._memoizedTables={};var K=Pn(A);z.del.forEach(function(Y){K[Y]=O[Y]}),yo(y,[y.Transaction.prototype]),la(y,[y.Transaction.prototype],d(K),K),b.schema=K;var N,L=Xn(B);return L&&$r(),z=X.follow(function(){var Y;(N=B(b))&&L&&(Y=Dt.bind(null,null),N.then(Y,Y))}),N&&typeof N.then=="function"?X.resolve(N):z.then(function(){return N})}}),U.push(function(O){var A,z,B=S._cfg.dbschema;A=B,z=O,[].slice.call(z.db.objectStoreNames).forEach(function(K){return A[K]==null&&z.db.deleteObjectStore(K)}),yo(y,[y.Transaction.prototype]),la(y,[y.Transaction.prototype],y._storeNames,y._dbSchema),b.schema=y._dbSchema}),U.push(function(O){y.idbdb.objectStoreNames.contains("$meta")&&(Math.ceil(y.idbdb.version/10)===S._cfg.version?(y.idbdb.deleteObjectStore("$meta"),delete y._dbSchema.$meta,y._storeNames=y._storeNames.filter(function(A){return A!=="$meta"})):O.objectStore("$meta").put(S._cfg.version,"version"))})}),(function S(){return U.length?X.resolve(U.shift()(b.idbtrans)).then(S):X.resolve()})().then(function(){Cl(P,x)})):X.resolve();var y,E,b,x,U,P}).catch(h)):(d(l).forEach(function(k){go(a,k,l[k].primKey,l[k].indexes)}),sa(n,a),void X.follow(function(){return n.on.populate.fire(u)}).catch(h));var g,_})}function ad(n,e){Cl(n._dbSchema,e),e.db.version%10!=0||e.objectStoreNames.contains("$meta")||e.db.createObjectStore("$meta").add(Math.ceil(e.db.version/10-1),"version");var a=ua(0,n.idbdb,e);ca(n,n._dbSchema,e);for(var o=0,l=wo(a,n._dbSchema).change;o<l.length;o++){var u=(function(h){if(h.change.length||h.recreate)return console.warn("Unable to patch indexes of table ".concat(h.name," because it has changes on the type of index or primary key.")),{value:void 0};var v=e.objectStore(h.name);h.add.forEach(function(g){Fe&&console.debug("Dexie upgrade patch: Creating missing index ".concat(h.name,".").concat(g.src)),fa(v,g)})})(l[o]);if(typeof u=="object")return u.value}}function wo(n,e){var a,o={del:[],add:[],change:[]};for(a in n)e[a]||o.del.push(a);for(a in e){var l=n[a],u=e[a];if(l){var h={name:a,def:u,recreate:!1,del:[],add:[],change:[]};if(""+(l.primKey.keyPath||"")!=""+(u.primKey.keyPath||"")||l.primKey.auto!==u.primKey.auto)h.recreate=!0,o.change.push(h);else{var v=l.idxByName,g=u.idxByName,_=void 0;for(_ in v)g[_]||h.del.push(_);for(_ in g){var k=v[_],y=g[_];k?k.src!==y.src&&h.change.push(y):h.add.push(y)}(0<h.del.length||0<h.add.length||0<h.change.length)&&o.change.push(h)}}else o.add.push([a,u])}return o}function go(n,e,a,o){var l=n.db.createObjectStore(e,a.keyPath?{keyPath:a.keyPath,autoIncrement:a.auto}:{autoIncrement:a.auto});return o.forEach(function(u){return fa(l,u)}),l}function Cl(n,e){d(n).forEach(function(a){e.db.objectStoreNames.contains(a)||(Fe&&console.debug("Dexie: Creating missing table",a),go(e,a,n[a].primKey,n[a].indexes))})}function fa(n,e){n.createIndex(e.name,e.keyPath,{unique:e.unique,multiEntry:e.multi})}function ua(n,e,a){var o={};return W(e.objectStoreNames,0).forEach(function(l){for(var u=a.objectStore(l),h=mo(Ol(_=u.keyPath),_||"",!0,!1,!!u.autoIncrement,_&&typeof _!="string",!0),v=[],g=0;g<u.indexNames.length;++g){var k=u.index(u.indexNames[g]),_=k.keyPath,k=mo(k.name,_,!!k.unique,!!k.multiEntry,!1,_&&typeof _!="string",!1);v.push(k)}o[l]=vo(l,h,v)}),o}function ca(n,e,a){for(var o=a.db.objectStoreNames,l=0;l<o.length;++l){var u=o[l],h=a.objectStore(u);n._hasGetAll="getAll"in h;for(var v=0;v<h.indexNames.length;++v){var g=h.indexNames[v],_=h.index(g).keyPath,k=typeof _=="string"?_:"["+W(_).join("+")+"]";!e[u]||(_=e[u].idxByName[k])&&(_.name=g,delete e[u].idxByName[k],e[u].idxByName[g]=_)}}typeof navigator<"u"&&/Safari/.test(navigator.userAgent)&&!/(Chrome\/|Edge\/)/.test(navigator.userAgent)&&c.WorkerGlobalScope&&c instanceof c.WorkerGlobalScope&&[].concat(navigator.userAgent.match(/Safari\/(\d*)/))[1]<604&&(n._hasGetAll=!1)}function zl(n){return n.split(",").map(function(e,a){var u=e.split(":"),o=(l=u[1])===null||l===void 0?void 0:l.trim(),l=(e=u[0].trim()).replace(/([&*]|\+\+)/g,""),u=/^\[/.test(l)?l.match(/^\[(.*)\]$/)[1].split("+"):l;return mo(l,u||null,/\&/.test(e),/\*/.test(e),/\+\+/.test(e),m(u),a===0,o)})}var od=(Nr.prototype._createTableSchema=vo,Nr.prototype._parseIndexSyntax=zl,Nr.prototype._parseStoresSpec=function(n,e){var a=this;d(n).forEach(function(o){if(n[o]!==null){var l=a._parseIndexSyntax(n[o]),u=l.shift();if(!u)throw new tn.Schema("Invalid schema for table "+o+": "+n[o]);if(u.unique=!0,u.multi)throw new tn.Schema("Primary key cannot be multiEntry*");l.forEach(function(h){if(h.auto)throw new tn.Schema("Only primary key can be marked as autoIncrement (++)");if(!h.keyPath)throw new tn.Schema("Index must have a name and cannot be an empty string")}),l=a._createTableSchema(o,u,l),e[o]=l}})},Nr.prototype.stores=function(a){var e=this.db;this._cfg.storesSource=this._cfg.storesSource?p(this._cfg.storesSource,a):a;var a=e._versions,o={},l={};return a.forEach(function(u){p(o,u._cfg.storesSource),l=u._cfg.dbschema={},u._parseStoresSpec(o,l)}),e._dbSchema=l,yo(e,[e._allTables,e,e.Transaction.prototype]),la(e,[e._allTables,e,e.Transaction.prototype,this._cfg.tables],d(l),l),e._storeNames=d(l),this},Nr.prototype.upgrade=function(n){return this._cfg.contentUpgrade=Ar(this._cfg.contentUpgrade||On,n),this},Nr);function Nr(){}function bo(n,e){var a=n._dbNamesDB;return a||(a=n._dbNamesDB=new wt(ea,{addons:[],indexedDB:n,IDBKeyRange:e})).version(1).stores({dbnames:"name"}),a.table("dbnames")}function _o(n){return n&&typeof n.databases=="function"}function xo(n){return zt(function(){return rn.letThrough=!0,n()})}function ko(n){return!("from"in n)}var be=function(n,e){if(!this){var a=new be;return n&&"d"in n&&p(a,n),a}p(this,arguments.length?{d:1,from:n,to:1<arguments.length?e:n}:{d:0})};function pi(n,e,a){var o=jn(e,a);if(!isNaN(o)){if(0<o)throw RangeError();if(ko(n))return p(n,{from:e,to:a,d:1});var l=n.l,o=n.r;if(jn(a,n.from)<0)return l?pi(l,e,a):n.l={from:e,to:a,d:1,l:null,r:null},Rl(n);if(0<jn(e,n.to))return o?pi(o,e,a):n.r={from:e,to:a,d:1,l:null,r:null},Rl(n);jn(e,n.from)<0&&(n.from=e,n.l=null,n.d=o?o.d+1:1),0<jn(a,n.to)&&(n.to=a,n.r=null,n.d=n.l?n.l.d+1:1),a=!n.r,l&&!n.l&&yi(n,l),o&&a&&yi(n,o)}}function yi(n,e){ko(e)||(function a(o,g){var u=g.from,h=g.to,v=g.l,g=g.r;pi(o,u,h),v&&a(o,v),g&&a(o,g)})(n,e)}function Dl(n,e){var a=da(e),o=a.next();if(o.done)return!1;for(var l=o.value,u=da(n),h=u.next(l.from),v=h.value;!o.done&&!h.done;){if(jn(v.from,l.to)<=0&&0<=jn(v.to,l.from))return!0;jn(l.from,v.from)<0?l=(o=a.next(v.from)).value:v=(h=u.next(l.from)).value}return!1}function da(n){var e=ko(n)?null:{s:0,n};return{next:function(a){for(var o=0<arguments.length;e;)switch(e.s){case 0:if(e.s=1,o)for(;e.n.l&&jn(a,e.n.from)<0;)e={up:e,n:e.n.l,s:1};else for(;e.n.l;)e={up:e,n:e.n.l,s:1};case 1:if(e.s=2,!o||jn(a,e.n.to)<=0)return{value:e.n,done:!1};case 2:if(e.n.r){e.s=3,e={up:e,n:e.n.r,s:0};continue}case 3:e=e.up}return{done:!0}}}}function Rl(n){var e,a,o=(((e=n.r)===null||e===void 0?void 0:e.d)||0)-(((a=n.l)===null||a===void 0?void 0:a.d)||0),l=1<o?"r":o<-1?"l":"";l&&(e=l=="r"?"l":"r",a=s({},n),o=n[l],n.from=o.from,n.to=o.to,n[l]=o[l],a[l]=o[e],(n[e]=a).d=$l(a)),n.d=$l(n)}function $l(a){var e=a.r,a=a.l;return(e?a?Math.max(e.d,a.d):e.d:a?a.d:0)+1}function ha(n,e){return d(e).forEach(function(a){n[a]?yi(n[a],e[a]):n[a]=(function o(l){var u,h,v={};for(u in l)T(l,u)&&(h=l[u],v[u]=!h||typeof h!="object"||Cn.has(h.constructor)?h:o(h));return v})(e[a])}),n}function Po(n,e){return n.all||e.all||Object.keys(n).some(function(a){return e[a]&&Dl(e[a],n[a])})}j(be.prototype,((We={add:function(n){return yi(this,n),this},addKey:function(n){return pi(this,n,n),this},addKeys:function(n){var e=this;return n.forEach(function(a){return pi(e,a,a)}),this},hasKey:function(n){var e=da(this).next(n).value;return e&&jn(e.from,n)<=0&&0<=jn(e.to,n)}})[te]=function(){return da(this)},We));var dr={},Uo={},Eo=!1;function ma(n){ha(Uo,n),Eo||(Eo=!0,setTimeout(function(){Eo=!1,So(Uo,!(Uo={}))},0))}function So(n,e){e===void 0&&(e=!1);var a=new Set;if(n.all)for(var o=0,l=Object.values(dr);o<l.length;o++)Ml(h=l[o],n,a,e);else for(var u in n){var h,v=/^idb\:\/\/(.*)\/(.*)\//.exec(u);v&&(u=v[1],v=v[2],(h=dr["idb://".concat(u,"/").concat(v)])&&Ml(h,n,a,e))}a.forEach(function(g){return g()})}function Ml(n,e,a,o){for(var l=[],u=0,h=Object.entries(n.queries.query);u<h.length;u++){for(var v=h[u],g=v[0],_=[],k=0,y=v[1];k<y.length;k++){var E=y[k];Po(e,E.obsSet)?E.subscribers.forEach(function(P){return a.add(P)}):o&&_.push(E)}o&&l.push([g,_])}if(o)for(var b=0,x=l;b<x.length;b++){var U=x[b],g=U[0],_=U[1];n.queries.query[g]=_}}function sd(n){var e=n._state,a=n._deps.indexedDB;if(e.isBeingOpened||n.idbdb)return e.dbReadyPromise.then(function(){return e.dbOpenError?re(e.dbOpenError):n});e.isBeingOpened=!0,e.dbOpenError=null,e.openComplete=!1;var o=e.openCanceller,l=Math.round(10*n.verno),u=!1;function h(){if(e.openCanceller!==o)throw new tn.DatabaseClosed("db.open() was cancelled")}function v(){return new X(function(E,b){if(h(),!a)throw new tn.MissingAPI;var x=n.name,U=e.autoSchema||!l?a.open(x):a.open(x,l);if(!U)throw new tn.MissingAPI;U.onerror=ct(b),U.onblocked=Hn(n._fireOnBlocked),U.onupgradeneeded=Hn(function(P){var S;k=U.transaction,e.autoSchema&&!n._options.allowEmptyDB?(U.onerror=di,k.abort(),U.result.close(),(S=a.deleteDatabase(x)).onsuccess=S.onerror=Hn(function(){b(new tn.NoSuchDatabase("Database ".concat(x," doesnt exist")))})):(k.onerror=ct(b),P=P.oldVersion>Math.pow(2,62)?0:P.oldVersion,y=P<1,n.idbdb=U.result,u&&ad(n,k),id(n,P/10,k,b))},b),U.onsuccess=Hn(function(){k=null;var P,S,O,A,z,B=n.idbdb=U.result,K=W(B.objectStoreNames);if(0<K.length)try{var N=B.transaction((A=K).length===1?A[0]:A,"readonly");if(e.autoSchema)S=B,O=N,(P=n).verno=S.version/10,O=P._dbSchema=ua(0,S,O),P._storeNames=W(S.objectStoreNames,0),la(P,[P._allTables],d(O),O);else if(ca(n,n._dbSchema,N),((z=wo(ua(0,(z=n).idbdb,N),z._dbSchema)).add.length||z.change.some(function(L){return L.add.length||L.change.length}))&&!u)return console.warn("Dexie SchemaDiff: Schema was extended without increasing the number passed to db.version(). Dexie will add missing parts and increment native version number to workaround this."),B.close(),l=B.version+1,u=!0,E(v());sa(n,N)}catch{}Mr.push(n),B.onversionchange=Hn(function(L){e.vcFired=!0,n.on("versionchange").fire(L)}),B.onclose=Hn(function(L){n.on("close").fire(L)}),y&&(z=n._deps,N=x,B=z.indexedDB,z=z.IDBKeyRange,_o(B)||N===ea||bo(B,z).put({name:N}).catch(On)),E()},b)}).catch(function(E){switch(E==null?void 0:E.name){case"UnknownError":if(0<e.PR1398_maxLoop)return e.PR1398_maxLoop--,console.warn("Dexie: Workaround for Chrome UnknownError on open()"),v();break;case"VersionError":if(0<l)return l=0,v()}return X.reject(E)})}var g,_=e.dbReadyResolve,k=null,y=!1;return X.race([o,(typeof navigator>"u"?X.resolve():!navigator.userAgentData&&/Safari\//.test(navigator.userAgent)&&!/Chrom(e|ium)\//.test(navigator.userAgent)&&indexedDB.databases?new Promise(function(E){function b(){return indexedDB.databases().finally(E)}g=setInterval(b,100),b()}).finally(function(){return clearInterval(g)}):Promise.resolve()).then(v)]).then(function(){return h(),e.onReadyBeingFired=[],X.resolve(xo(function(){return n.on.ready.fire(n.vip)})).then(function E(){if(0<e.onReadyBeingFired.length){var b=e.onReadyBeingFired.reduce(Ar,On);return e.onReadyBeingFired=[],X.resolve(xo(function(){return b(n.vip)})).then(E)}})}).finally(function(){e.openCanceller===o&&(e.onReadyBeingFired=null,e.isBeingOpened=!1)}).catch(function(E){e.dbOpenError=E;try{k&&k.abort()}catch{}return o===e.openCanceller&&n._close(),re(E)}).finally(function(){e.openComplete=!0,_()}).then(function(){var E;return y&&(E={},n.tables.forEach(function(b){b.schema.indexes.forEach(function(x){x.name&&(E["idb://".concat(n.name,"/").concat(b.name,"/").concat(x.name)]=new be(-1/0,[[[]]]))}),E["idb://".concat(n.name,"/").concat(b.name,"/")]=E["idb://".concat(n.name,"/").concat(b.name,"/:dels")]=new be(-1/0,[[[]]])}),Mt(hi).fire(E),So(E,!0)),n})}function jo(n){function e(u){return n.next(u)}var a=l(e),o=l(function(u){return n.throw(u)});function l(u){return function(g){var v=u(g),g=v.value;return v.done?g:g&&typeof g.then=="function"?g.then(a,o):m(g)?Promise.all(g).then(a,o):a(g)}}return l(e)()}function va(n,e,a){for(var o=m(n)?n.slice():[n],l=0;l<a;++l)o.push(e);return o}var ld={stack:"dbcore",name:"VirtualIndexMiddleware",level:1,create:function(n){return s(s({},n),{table:function(e){var a=n.table(e),o=a.schema,l={},u=[];function h(y,E,b){var x=vi(y),U=l[x]=l[x]||[],P=y==null?0:typeof y=="string"?1:y.length,S=0<E,S=s(s({},b),{name:S?"".concat(x,"(virtual-from:").concat(b.name,")"):b.name,lowLevelIndex:b,isVirtual:S,keyTail:E,keyLength:P,extractKey:po(y),unique:!S&&b.unique});return U.push(S),S.isPrimaryKey||u.push(S),1<P&&h(P===2?y[0]:y.slice(0,P-1),E+1,b),U.sort(function(O,A){return O.keyTail-A.keyTail}),S}e=h(o.primaryKey.keyPath,0,o.primaryKey),l[":id"]=[e];for(var v=0,g=o.indexes;v<g.length;v++){var _=g[v];h(_.keyPath,0,_)}function k(y){var E,b=y.query.index;return b.isVirtual?s(s({},y),{query:{index:b.lowLevelIndex,range:(E=y.query.range,b=b.keyTail,{type:E.type===1?2:E.type,lower:va(E.lower,E.lowerOpen?n.MAX_KEY:n.MIN_KEY,b),lowerOpen:!0,upper:va(E.upper,E.upperOpen?n.MIN_KEY:n.MAX_KEY,b),upperOpen:!0})}}):y}return s(s({},a),{schema:s(s({},o),{primaryKey:e,indexes:u,getIndexByKeyPath:function(y){return(y=l[vi(y)])&&y[0]}}),count:function(y){return a.count(k(y))},query:function(y){return a.query(k(y))},openCursor:function(y){var E=y.query.index,b=E.keyTail,x=E.isVirtual,U=E.keyLength;return x?a.openCursor(k(y)).then(function(S){return S&&P(S)}):a.openCursor(y);function P(S){return Object.create(S,{continue:{value:function(O){O!=null?S.continue(va(O,y.reverse?n.MAX_KEY:n.MIN_KEY,b)):y.unique?S.continue(S.key.slice(0,U).concat(y.reverse?n.MIN_KEY:n.MAX_KEY,b)):S.continue()}},continuePrimaryKey:{value:function(O,A){S.continuePrimaryKey(va(O,n.MAX_KEY,b),A)}},primaryKey:{get:function(){return S.primaryKey}},key:{get:function(){var O=S.key;return U===1?O[0]:O.slice(0,U)}},value:{get:function(){return S.value}}})}}})}})}};function To(n,e,a,o){return a=a||{},o=o||"",d(n).forEach(function(l){var u,h,v;T(e,l)?(u=n[l],h=e[l],typeof u=="object"&&typeof h=="object"&&u&&h?(v=sn(u))!==sn(h)?a[o+l]=e[l]:v==="Object"?To(u,h,a,o+l+"."):u!==h&&(a[o+l]=e[l]):u!==h&&(a[o+l]=e[l])):a[o+l]=void 0}),d(e).forEach(function(l){T(n,l)||(a[o+l]=e[l])}),a}function Io(n,e){return e.type==="delete"?e.keys:e.keys||e.values.map(n.extractKey)}var fd={stack:"dbcore",name:"HooksMiddleware",level:2,create:function(n){return s(s({},n),{table:function(e){var a=n.table(e),o=a.schema.primaryKey;return s(s({},a),{mutate:function(l){var u=rn.trans,h=u.table(e).hook,v=h.deleting,g=h.creating,_=h.updating;switch(l.type){case"add":if(g.fire===On)break;return u._promise("readwrite",function(){return k(l)},!0);case"put":if(g.fire===On&&_.fire===On)break;return u._promise("readwrite",function(){return k(l)},!0);case"delete":if(v.fire===On)break;return u._promise("readwrite",function(){return k(l)},!0);case"deleteRange":if(v.fire===On)break;return u._promise("readwrite",function(){return(function y(E,b,x){return a.query({trans:E,values:!1,query:{index:o,range:b},limit:x}).then(function(U){var P=U.result;return k({type:"delete",keys:P,trans:E}).then(function(S){return 0<S.numFailures?Promise.reject(S.failures[0]):P.length<x?{failures:[],numFailures:0,lastResult:void 0}:y(E,s(s({},b),{lower:P[P.length-1],lowerOpen:!0}),x)})})})(l.trans,l.range,1e4)},!0)}return a.mutate(l);function k(y){var E,b,x,U=rn.trans,P=y.keys||Io(o,y);if(!P)throw new Error("Keys missing");return(y=y.type==="add"||y.type==="put"?s(s({},y),{keys:P}):s({},y)).type!=="delete"&&(y.values=f([],y.values)),y.keys&&(y.keys=f([],y.keys)),E=a,x=P,((b=y).type==="add"?Promise.resolve([]):E.getMany({trans:b.trans,keys:x,cache:"immutable"})).then(function(S){var O=P.map(function(A,z){var B,K,N,L=S[z],Y={onerror:null,onsuccess:null};return y.type==="delete"?v.fire.call(Y,A,L,U):y.type==="add"||L===void 0?(B=g.fire.call(Y,A,y.values[z],U),A==null&&B!=null&&(y.keys[z]=A=B,o.outbound||Z(y.values[z],o.keyPath,A))):(B=To(L,y.values[z]),(K=_.fire.call(Y,B,A,L,U))&&(N=y.values[z],Object.keys(K).forEach(function(G){T(N,G)?N[G]=K[G]:Z(N,G,K[G])}))),Y});return a.mutate(y).then(function(A){for(var z=A.failures,B=A.results,K=A.numFailures,A=A.lastResult,N=0;N<P.length;++N){var L=(B||P)[N],Y=O[N];L==null?Y.onerror&&Y.onerror(z[N]):Y.onsuccess&&Y.onsuccess(y.type==="put"&&S[N]?y.values[N]:L)}return{failures:z,results:B,numFailures:K,lastResult:A}}).catch(function(A){return O.forEach(function(z){return z.onerror&&z.onerror(A)}),Promise.reject(A)})})}}})}})}};function Fl(n,e,a){try{if(!e||e.keys.length<n.length)return null;for(var o=[],l=0,u=0;l<e.keys.length&&u<n.length;++l)jn(e.keys[l],n[u])===0&&(o.push(a?Un(e.values[l]):e.values[l]),++u);return o.length===n.length?o:null}catch{return null}}var ud={stack:"dbcore",level:-1,create:function(n){return{table:function(e){var a=n.table(e);return s(s({},a),{getMany:function(o){if(!o.cache)return a.getMany(o);var l=Fl(o.keys,o.trans._cache,o.cache==="clone");return l?X.resolve(l):a.getMany(o).then(function(u){return o.trans._cache={keys:o.keys,values:o.cache==="clone"?Un(u):u},u})},mutate:function(o){return o.type!=="add"&&(o.trans._cache=null),a.mutate(o)}})}}}};function Bl(n,e){return n.trans.mode==="readonly"&&!!n.subscr&&!n.trans.explicit&&n.trans.db._options.cache!=="disabled"&&!e.schema.primaryKey.outbound}function Nl(n,e){switch(n){case"query":return e.values&&!e.unique;case"get":case"getMany":case"count":case"openCursor":return!1}}var cd={stack:"dbcore",level:0,name:"Observability",create:function(n){var e=n.schema.name,a=new be(n.MIN_KEY,n.MAX_KEY);return s(s({},n),{transaction:function(o,l,u){if(rn.subscr&&l!=="readonly")throw new tn.ReadOnly("Readwrite transaction in liveQuery context. Querier source: ".concat(rn.querier));return n.transaction(o,l,u)},table:function(o){var l=n.table(o),u=l.schema,h=u.primaryKey,y=u.indexes,v=h.extractKey,g=h.outbound,_=h.autoIncrement&&y.filter(function(b){return b.compound&&b.keyPath.includes(h.keyPath)}),k=s(s({},l),{mutate:function(b){function x(G){return G="idb://".concat(e,"/").concat(o,"/").concat(G),A[G]||(A[G]=new be)}var U,P,S,O=b.trans,A=b.mutatedParts||(b.mutatedParts={}),z=x(""),B=x(":dels"),K=b.type,Y=b.type==="deleteRange"?[b.range]:b.type==="delete"?[b.keys]:b.values.length<50?[Io(h,b).filter(function(G){return G}),b.values]:[],N=Y[0],L=Y[1],Y=b.trans._cache;return m(N)?(z.addKeys(N),(Y=K==="delete"||N.length===L.length?Fl(N,Y):null)||B.addKeys(N),(Y||L)&&(U=x,P=Y,S=L,u.indexes.forEach(function(G){var J=U(G.name||"");function yn(dn){return dn!=null?G.extractKey(dn):null}function Tn(dn){return G.multiEntry&&m(dn)?dn.forEach(function(Jn){return J.addKey(Jn)}):J.addKey(dn)}(P||S).forEach(function(dn,_e){var fn=P&&yn(P[_e]),_e=S&&yn(S[_e]);jn(fn,_e)!==0&&(fn!=null&&Tn(fn),_e!=null&&Tn(_e))})}))):N?(L={from:(L=N.lower)!==null&&L!==void 0?L:n.MIN_KEY,to:(L=N.upper)!==null&&L!==void 0?L:n.MAX_KEY},B.add(L),z.add(L)):(z.add(a),B.add(a),u.indexes.forEach(function(G){return x(G.name).add(a)})),l.mutate(b).then(function(G){return!N||b.type!=="add"&&b.type!=="put"||(z.addKeys(G.results),_&&_.forEach(function(J){for(var yn=b.values.map(function(fn){return J.extractKey(fn)}),Tn=J.keyPath.findIndex(function(fn){return fn===h.keyPath}),dn=0,Jn=G.results.length;dn<Jn;++dn)yn[dn][Tn]=G.results[dn];x(J.name).addKeys(yn)})),O.mutatedParts=ha(O.mutatedParts||{},A),G})}}),y=function(x){var U=x.query,x=U.index,U=U.range;return[x,new be((x=U.lower)!==null&&x!==void 0?x:n.MIN_KEY,(U=U.upper)!==null&&U!==void 0?U:n.MAX_KEY)]},E={get:function(b){return[h,new be(b.key)]},getMany:function(b){return[h,new be().addKeys(b.keys)]},count:y,query:y,openCursor:y};return d(E).forEach(function(b){k[b]=function(x){var U=rn.subscr,P=!!U,S=Bl(rn,l)&&Nl(b,x)?x.obsSet={}:U;if(P){var O=function(L){return L="idb://".concat(e,"/").concat(o,"/").concat(L),S[L]||(S[L]=new be)},A=O(""),z=O(":dels"),U=E[b](x),P=U[0],U=U[1];if((b==="query"&&P.isPrimaryKey&&!x.values?z:O(P.name||"")).add(U),!P.isPrimaryKey){if(b!=="count"){var B=b==="query"&&g&&x.values&&l.query(s(s({},x),{values:!1}));return l[b].apply(this,arguments).then(function(L){if(b==="query"){if(g&&x.values)return B.then(function(yn){return yn=yn.result,A.addKeys(yn),L});var Y=x.values?L.result.map(v):L.result;(x.values?A:z).addKeys(Y)}else if(b==="openCursor"){var G=L,J=x.values;return G&&Object.create(G,{key:{get:function(){return z.addKey(G.primaryKey),G.key}},primaryKey:{get:function(){var yn=G.primaryKey;return z.addKey(yn),yn}},value:{get:function(){return J&&A.addKey(G.primaryKey),G.value}}})}return L})}z.add(a)}}return l[b].apply(this,arguments)}}),k}})}};function Ll(n,e,a){if(a.numFailures===0)return e;if(e.type==="deleteRange")return null;var o=e.keys?e.keys.length:"values"in e&&e.values?e.values.length:1;return a.numFailures===o?null:(e=s({},e),m(e.keys)&&(e.keys=e.keys.filter(function(l,u){return!(u in a.failures)})),"values"in e&&m(e.values)&&(e.values=e.values.filter(function(l,u){return!(u in a.failures)})),e)}function Oo(n,e){return a=n,((o=e).lower===void 0||(o.lowerOpen?0<jn(a,o.lower):0<=jn(a,o.lower)))&&(n=n,(e=e).upper===void 0||(e.upperOpen?jn(n,e.upper)<0:jn(n,e.upper)<=0));var a,o}function Kl(n,e,E,o,l,u){if(!E||E.length===0)return n;var h=e.query.index,v=h.multiEntry,g=e.query.range,_=o.schema.primaryKey.extractKey,k=h.extractKey,y=(h.lowLevelIndex||h).extractKey,E=E.reduce(function(b,x){var U=b,P=[];if(x.type==="add"||x.type==="put")for(var S=new be,O=x.values.length-1;0<=O;--O){var A,z=x.values[O],B=_(z);S.hasKey(B)||(A=k(z),(v&&m(A)?A.some(function(G){return Oo(G,g)}):Oo(A,g))&&(S.addKey(B),P.push(z)))}switch(x.type){case"add":var K=new be().addKeys(e.values?b.map(function(J){return _(J)}):b),U=b.concat(e.values?P.filter(function(J){return J=_(J),!K.hasKey(J)&&(K.addKey(J),!0)}):P.map(function(J){return _(J)}).filter(function(J){return!K.hasKey(J)&&(K.addKey(J),!0)}));break;case"put":var N=new be().addKeys(x.values.map(function(J){return _(J)}));U=b.filter(function(J){return!N.hasKey(e.values?_(J):J)}).concat(e.values?P:P.map(function(J){return _(J)}));break;case"delete":var L=new be().addKeys(x.keys);U=b.filter(function(J){return!L.hasKey(e.values?_(J):J)});break;case"deleteRange":var Y=x.range;U=b.filter(function(J){return!Oo(_(J),Y)})}return U},n);return E===n?n:(E.sort(function(b,x){return jn(y(b),y(x))||jn(_(b),_(x))}),e.limit&&e.limit<1/0&&(E.length>e.limit?E.length=e.limit:n.length===e.limit&&E.length<e.limit&&(l.dirty=!0)),u?Object.freeze(E):E)}function ql(n,e){return jn(n.lower,e.lower)===0&&jn(n.upper,e.upper)===0&&!!n.lowerOpen==!!e.lowerOpen&&!!n.upperOpen==!!e.upperOpen}function dd(n,e){return(function(a,o,l,u){if(a===void 0)return o!==void 0?-1:0;if(o===void 0)return 1;if((o=jn(a,o))===0){if(l&&u)return 0;if(l)return 1;if(u)return-1}return o})(n.lower,e.lower,n.lowerOpen,e.lowerOpen)<=0&&0<=(function(a,o,l,u){if(a===void 0)return o!==void 0?1:0;if(o===void 0)return-1;if((o=jn(a,o))===0){if(l&&u)return 0;if(l)return-1;if(u)return 1}return o})(n.upper,e.upper,n.upperOpen,e.upperOpen)}function hd(n,e,a,o){n.subscribers.add(a),o.addEventListener("abort",function(){var l,u;n.subscribers.delete(a),n.subscribers.size===0&&(l=n,u=e,setTimeout(function(){l.subscribers.size===0&&En(u,l)},3e3))})}var md={stack:"dbcore",level:0,name:"Cache",create:function(n){var e=n.schema.name;return s(s({},n),{transaction:function(a,o,l){var u,h,v=n.transaction(a,o,l);return o==="readwrite"&&(h=(u=new AbortController).signal,l=function(g){return function(){if(u.abort(),o==="readwrite"){for(var _=new Set,k=0,y=a;k<y.length;k++){var E=y[k],b=dr["idb://".concat(e,"/").concat(E)];if(b){var x=n.table(E),U=b.optimisticOps.filter(function(J){return J.trans===v});if(v._explicit&&g&&v.mutatedParts)for(var P=0,S=Object.values(b.queries.query);P<S.length;P++)for(var O=0,A=(K=S[P]).slice();O<A.length;O++)Po((N=A[O]).obsSet,v.mutatedParts)&&(En(K,N),N.subscribers.forEach(function(J){return _.add(J)}));else if(0<U.length){b.optimisticOps=b.optimisticOps.filter(function(J){return J.trans!==v});for(var z=0,B=Object.values(b.queries.query);z<B.length;z++)for(var K,N,L,Y=0,G=(K=B[z]).slice();Y<G.length;Y++)(N=G[Y]).res!=null&&v.mutatedParts&&(g&&!N.dirty?(L=Object.isFrozen(N.res),L=Kl(N.res,N.req,U,x,N,L),N.dirty?(En(K,N),N.subscribers.forEach(function(J){return _.add(J)})):L!==N.res&&(N.res=L,N.promise=X.resolve({result:L}))):(N.dirty&&En(K,N),N.subscribers.forEach(function(J){return _.add(J)})))}}}_.forEach(function(J){return J()})}}},v.addEventListener("abort",l(!1),{signal:h}),v.addEventListener("error",l(!1),{signal:h}),v.addEventListener("complete",l(!0),{signal:h})),v},table:function(a){var o=n.table(a),l=o.schema.primaryKey;return s(s({},o),{mutate:function(u){var h=rn.trans;if(l.outbound||h.db._options.cache==="disabled"||h.explicit||h.idbtrans.mode!=="readwrite")return o.mutate(u);var v=dr["idb://".concat(e,"/").concat(a)];return v?(h=o.mutate(u),u.type!=="add"&&u.type!=="put"||!(50<=u.values.length||Io(l,u).some(function(g){return g==null}))?(v.optimisticOps.push(u),u.mutatedParts&&ma(u.mutatedParts),h.then(function(g){0<g.numFailures&&(En(v.optimisticOps,u),(g=Ll(0,u,g))&&v.optimisticOps.push(g),u.mutatedParts&&ma(u.mutatedParts))}),h.catch(function(){En(v.optimisticOps,u),u.mutatedParts&&ma(u.mutatedParts)})):h.then(function(g){var _=Ll(0,s(s({},u),{values:u.values.map(function(k,y){var E;return g.failures[y]?k:(k=(E=l.keyPath)!==null&&E!==void 0&&E.includes(".")?Un(k):s({},k),Z(k,l.keyPath,g.results[y]),k)})}),g);v.optimisticOps.push(_),queueMicrotask(function(){return u.mutatedParts&&ma(u.mutatedParts)})}),h):o.mutate(u)},query:function(u){if(!Bl(rn,o)||!Nl("query",u))return o.query(u);var h=((_=rn.trans)===null||_===void 0?void 0:_.db._options.cache)==="immutable",y=rn,v=y.requery,g=y.signal,_=(function(x,U,P,S){var O=dr["idb://".concat(x,"/").concat(U)];if(!O)return[];if(!(U=O.queries[P]))return[null,!1,O,null];var A=U[(S.query?S.query.index.name:null)||""];if(!A)return[null,!1,O,null];switch(P){case"query":var z=A.find(function(B){return B.req.limit===S.limit&&B.req.values===S.values&&ql(B.req.query.range,S.query.range)});return z?[z,!0,O,A]:[A.find(function(B){return("limit"in B.req?B.req.limit:1/0)>=S.limit&&(!S.values||B.req.values)&&dd(B.req.query.range,S.query.range)}),!1,O,A];case"count":return z=A.find(function(B){return ql(B.req.query.range,S.query.range)}),[z,!!z,O,A]}})(e,a,"query",u),k=_[0],y=_[1],E=_[2],b=_[3];return k&&y?k.obsSet=u.obsSet:(y=o.query(u).then(function(x){var U=x.result;if(k&&(k.res=U),h){for(var P=0,S=U.length;P<S;++P)Object.freeze(U[P]);Object.freeze(U)}else x.result=Un(U);return x}).catch(function(x){return b&&k&&En(b,k),Promise.reject(x)}),k={obsSet:u.obsSet,promise:y,subscribers:new Set,type:"query",req:u,dirty:!1},b?b.push(k):(b=[k],(E=E||(dr["idb://".concat(e,"/").concat(a)]={queries:{query:{},count:{}},objs:new Map,optimisticOps:[],unsignaledParts:{}})).queries.query[u.query.index.name||""]=b)),hd(k,b,v,g),k.promise.then(function(x){return{result:Kl(x.result,u,E==null?void 0:E.optimisticOps,o,k,h)}})}})}})}};function pa(n,e){return new Proxy(n,{get:function(a,o,l){return o==="db"?e:Reflect.get(a,o,l)}})}var wt=(ie.prototype.version=function(n){if(isNaN(n)||n<.1)throw new tn.Type("Given version is not a positive number");if(n=Math.round(10*n)/10,this.idbdb||this._state.isBeingOpened)throw new tn.Schema("Cannot add version when database is open");this.verno=Math.max(this.verno,n);var e=this._versions,a=e.filter(function(o){return o._cfg.version===n})[0];return a||(a=new this.Version(n),e.push(a),e.sort(rd),a.stores({}),this._state.autoSchema=!1,a)},ie.prototype._whenReady=function(n){var e=this;return this.idbdb&&(this._state.openComplete||rn.letThrough||this._vip)?n():new X(function(a,o){if(e._state.openComplete)return o(new tn.DatabaseClosed(e._state.dbOpenError));if(!e._state.isBeingOpened){if(!e._state.autoOpen)return void o(new tn.DatabaseClosed);e.open().catch(On)}e._state.dbReadyPromise.then(a,o)}).then(n)},ie.prototype.use=function(n){var e=n.stack,a=n.create,o=n.level,l=n.name;return l&&this.unuse({stack:e,name:l}),n=this._middlewares[e]||(this._middlewares[e]=[]),n.push({stack:e,create:a,level:o??10,name:l}),n.sort(function(u,h){return u.level-h.level}),this},ie.prototype.unuse=function(n){var e=n.stack,a=n.name,o=n.create;return e&&this._middlewares[e]&&(this._middlewares[e]=this._middlewares[e].filter(function(l){return o?l.create!==o:!!a&&l.name!==a})),this},ie.prototype.open=function(){var n=this;return fr(Ct,function(){return sd(n)})},ie.prototype._close=function(){this.on.close.fire(new CustomEvent("close"));var n=this._state,e=Mr.indexOf(this);if(0<=e&&Mr.splice(e,1),this.idbdb){try{this.idbdb.close()}catch{}this.idbdb=null}n.isBeingOpened||(n.dbReadyPromise=new X(function(a){n.dbReadyResolve=a}),n.openCanceller=new X(function(a,o){n.cancelOpen=o}))},ie.prototype.close=function(a){var e=(a===void 0?{disableAutoOpen:!0}:a).disableAutoOpen,a=this._state;e?(a.isBeingOpened&&a.cancelOpen(new tn.DatabaseClosed),this._close(),a.autoOpen=!1,a.dbOpenError=new tn.DatabaseClosed):(this._close(),a.autoOpen=this._options.autoOpen||a.isBeingOpened,a.openComplete=!1,a.dbOpenError=null)},ie.prototype.delete=function(n){var e=this;n===void 0&&(n={disableAutoOpen:!0});var a=0<arguments.length&&typeof arguments[0]!="object",o=this._state;return new X(function(l,u){function h(){e.close(n);var v=e._deps.indexedDB.deleteDatabase(e.name);v.onsuccess=Hn(function(){var g,_,k;g=e._deps,_=e.name,k=g.indexedDB,g=g.IDBKeyRange,_o(k)||_===ea||bo(k,g).delete(_).catch(On),l()}),v.onerror=ct(u),v.onblocked=e._fireOnBlocked}if(a)throw new tn.InvalidArgument("Invalid closeOptions argument to db.delete()");o.isBeingOpened?o.dbReadyPromise.then(h):h()})},ie.prototype.backendDB=function(){return this.idbdb},ie.prototype.isOpen=function(){return this.idbdb!==null},ie.prototype.hasBeenClosed=function(){var n=this._state.dbOpenError;return n&&n.name==="DatabaseClosed"},ie.prototype.hasFailed=function(){return this._state.dbOpenError!==null},ie.prototype.dynamicallyOpened=function(){return this._state.autoSchema},Object.defineProperty(ie.prototype,"tables",{get:function(){var n=this;return d(this._allTables).map(function(e){return n._allTables[e]})},enumerable:!1,configurable:!0}),ie.prototype.transaction=function(){var n=(function(e,a,o){var l=arguments.length;if(l<2)throw new tn.InvalidArgument("Too few arguments");for(var u=new Array(l-1);--l;)u[l-1]=arguments[l];return o=u.pop(),[e,Dn(u),o]}).apply(this,arguments);return this._transaction.apply(this,n)},ie.prototype._transaction=function(n,e,a){var o=this,l=rn.trans;l&&l.db===this&&n.indexOf("!")===-1||(l=null);var u,h,v=n.indexOf("?")!==-1;n=n.replace("!","").replace("?","");try{if(h=e.map(function(_){if(_=_ instanceof o.Table?_.name:_,typeof _!="string")throw new TypeError("Invalid table argument to Dexie.transaction(). Only Table or String are allowed");return _}),n=="r"||n===lo)u=lo;else{if(n!="rw"&&n!=fo)throw new tn.InvalidArgument("Invalid transaction mode: "+n);u=fo}if(l){if(l.mode===lo&&u===fo){if(!v)throw new tn.SubTransaction("Cannot enter a sub-transaction with READWRITE mode when parent transaction is READONLY");l=null}l&&h.forEach(function(_){if(l&&l.storeNames.indexOf(_)===-1){if(!v)throw new tn.SubTransaction("Table "+_+" not included in parent transaction.");l=null}}),v&&l&&!l.active&&(l=null)}}catch(_){return l?l._promise(null,function(k,y){y(_)}):re(_)}var g=(function _(k,y,E,b,x){return X.resolve().then(function(){var U=rn.transless||rn,P=k._createTransaction(y,E,k._dbSchema,b);if(P.explicit=!0,U={trans:P,transless:U},b)P.idbtrans=b.idbtrans;else try{P.create(),P.idbtrans._explicit=!0,k._state.PR1398_maxLoop=3}catch(A){return A.name===ve.InvalidState&&k.isOpen()&&0<--k._state.PR1398_maxLoop?(console.warn("Dexie: Need to reopen db"),k.close({disableAutoOpen:!1}),k.open().then(function(){return _(k,y,E,null,x)})):re(A)}var S,O=Xn(x);return O&&$r(),U=X.follow(function(){var A;(S=x.call(P,P))&&(O?(A=Dt.bind(null,null),S.then(A,A)):typeof S.next=="function"&&typeof S.throw=="function"&&(S=jo(S)))},U),(S&&typeof S.then=="function"?X.resolve(S).then(function(A){return P.active?A:re(new tn.PrematureCommit("Transaction committed too early. See http://bit.ly/2kdckMn"))}):U.then(function(){return S})).then(function(A){return b&&P._resolve(),P._completion.then(function(){return A})}).catch(function(A){return P._reject(A),re(A)})})}).bind(null,this,u,h,l,a);return l?l._promise(u,g,"lock"):rn.trans?fr(rn.transless,function(){return o._whenReady(g)}):this._whenReady(g)},ie.prototype.table=function(n){if(!T(this._allTables,n))throw new tn.InvalidTable("Table ".concat(n," does not exist"));return this._allTables[n]},ie);function ie(n,e){var a=this;this._middlewares={},this.verno=0;var o=ie.dependencies;this._options=e=s({addons:ie.addons,autoOpen:!0,indexedDB:o.indexedDB,IDBKeyRange:o.IDBKeyRange,cache:"cloned"},e),this._deps={indexedDB:e.indexedDB,IDBKeyRange:e.IDBKeyRange},o=e.addons,this._dbSchema={},this._versions=[],this._storeNames=[],this._allTables={},this.idbdb=null,this._novip=this;var l,u,h,v,g,_={dbOpenError:null,isBeingOpened:!1,onReadyBeingFired:null,openComplete:!1,dbReadyResolve:On,dbReadyPromise:null,cancelOpen:On,openCanceller:null,autoSchema:!0,PR1398_maxLoop:3,autoOpen:e.autoOpen};_.dbReadyPromise=new X(function(y){_.dbReadyResolve=y}),_.openCanceller=new X(function(y,E){_.cancelOpen=E}),this._state=_,this.name=n,this.on=fi(this,"populate","blocked","versionchange","close",{ready:[Ar,On]}),this.once=function(y,E){var b=function(){for(var x=[],U=0;U<arguments.length;U++)x[U]=arguments[U];a.on(y).unsubscribe(b),E.apply(a,x)};return a.on(y,b)},this.on.ready.subscribe=en(this.on.ready.subscribe,function(y){return function(E,b){ie.vip(function(){var x,U=a._state;U.openComplete?(U.dbOpenError||X.resolve().then(E),b&&y(E)):U.onReadyBeingFired?(U.onReadyBeingFired.push(E),b&&y(E)):(y(E),x=a,b||y(function P(){x.on.ready.unsubscribe(E),x.on.ready.unsubscribe(P)}))})}}),this.Collection=(l=this,ui(Xc.prototype,function(S,P){this.db=l;var b=bl,x=null;if(P)try{b=P()}catch(O){x=O}var U=S._ctx,P=U.table,S=P.hook.reading.fire;this._ctx={table:P,index:U.index,isPrimKey:!U.index||P.schema.primKey.keyPath&&U.index===P.schema.primKey.name,range:b,keysOnly:!1,dir:"next",unique:"",algorithm:null,filter:null,replayFilter:null,justLimit:!0,isMatch:null,offset:0,limit:1/0,error:x,or:U.or,valueMapper:S!==Tt?S:null}})),this.Table=(u=this,ui(Pl.prototype,function(y,E,b){this.db=u,this._tx=b,this.name=y,this.schema=E,this.hook=u._allTables[y]?u._allTables[y].hook:fi(null,{creating:[qi,On],reading:[Ki,Tt],updating:[Hi,On],deleting:[Vi,On]})})),this.Transaction=(h=this,ui(Zc.prototype,function(y,E,b,x,U){var P=this;y!=="readonly"&&E.forEach(function(S){S=(S=b[S])===null||S===void 0?void 0:S.yProps,S&&(E=E.concat(S.map(function(O){return O.updatesTable})))}),this.db=h,this.mode=y,this.storeNames=E,this.schema=b,this.chromeTransactionDurability=x,this.idbtrans=null,this.on=fi(this,"complete","error","abort"),this.parent=U||null,this.active=!0,this._reculock=0,this._blockedFuncs=[],this._resolve=null,this._reject=null,this._waitingFor=null,this._waitingQueue=null,this._spinCount=0,this._completion=new X(function(S,O){P._resolve=S,P._reject=O}),this._completion.then(function(){P.active=!1,P.on.complete.fire()},function(S){var O=P.active;return P.active=!1,P.on.error.fire(S),P.parent?P.parent._reject(S):O&&P.idbtrans&&P.idbtrans.abort(),re(S)})})),this.Version=(v=this,ui(od.prototype,function(y){this.db=v,this._cfg={version:y,storesSource:null,dbschema:{},tables:{},contentUpgrade:null}})),this.WhereClause=(g=this,ui(Il.prototype,function(y,E,b){if(this.db=g,this._ctx={table:y,index:E===":id"?null:E,or:b},this._cmp=this._ascending=jn,this._descending=function(x,U){return jn(U,x)},this._max=function(x,U){return 0<jn(x,U)?x:U},this._min=function(x,U){return jn(x,U)<0?x:U},this._IDBKeyRange=g._deps.IDBKeyRange,!this._IDBKeyRange)throw new tn.MissingAPI})),this.on("versionchange",function(y){0<y.newVersion?console.warn("Another connection wants to upgrade database '".concat(a.name,"'. Closing db now to resume the upgrade.")):console.warn("Another connection wants to delete database '".concat(a.name,"'. Closing db now to resume the delete request.")),a.close({disableAutoOpen:!1})}),this.on("blocked",function(y){!y.newVersion||y.newVersion<y.oldVersion?console.warn("Dexie.delete('".concat(a.name,"') was blocked")):console.warn("Upgrade '".concat(a.name,"' blocked by other connection holding version ").concat(y.oldVersion/10))}),this._maxKey=mi(e.IDBKeyRange),this._createTransaction=function(y,E,b,x){return new a.Transaction(y,E,b,a._options.chromeTransactionDurability,x)},this._fireOnBlocked=function(y){a.on("blocked").fire(y),Mr.filter(function(E){return E.name===a.name&&E!==a&&!E._state.vcFired}).map(function(E){return E.on("versionchange").fire(y)})},this.use(ud),this.use(md),this.use(cd),this.use(ld),this.use(fd);var k=new Proxy(this,{get:function(y,E,b){if(E==="_vip")return!0;if(E==="table")return function(U){return pa(a.table(U),k)};var x=Reflect.get(y,E,b);return x instanceof Pl?pa(x,k):E==="tables"?x.map(function(U){return pa(U,k)}):E==="_createTransaction"?function(){return pa(x.apply(this,arguments),k)}:x}});this.vip=k,o.forEach(function(y){return y(a)})}var ya,We=typeof Symbol<"u"&&"observable"in Symbol?Symbol.observable:"@@observable",vd=(Ao.prototype.subscribe=function(n,e,a){return this._subscribe(n&&typeof n!="function"?n:{next:n,error:e,complete:a})},Ao.prototype[We]=function(){return this},Ao);function Ao(n){this._subscribe=n}try{ya={indexedDB:c.indexedDB||c.mozIndexedDB||c.webkitIndexedDB||c.msIndexedDB,IDBKeyRange:c.IDBKeyRange||c.webkitIDBKeyRange}}catch{ya={indexedDB:null,IDBKeyRange:null}}function Vl(n){var e,a=!1,o=new vd(function(l){var u=Xn(n),h,v=!1,g={},_={},k={get closed(){return v},unsubscribe:function(){v||(v=!0,h&&h.abort(),y&&Mt.storagemutated.unsubscribe(b))}};l.start&&l.start(k);var y=!1,E=function(){return so(x)},b=function(U){ha(g,U),Po(_,g)&&E()},x=function(){var U,P,S;!v&&ya.indexedDB&&(g={},U={},h&&h.abort(),h=new AbortController,S=(function(O){var A=Dr();try{u&&$r();var z=zt(n,O);return z=u?z.finally(Dt):z}finally{A&&Rr()}})(P={subscr:U,signal:h.signal,requery:E,querier:n,trans:null}),Promise.resolve(S).then(function(O){a=!0,e=O,v||P.signal.aborted||(g={},(function(A){for(var z in A)if(T(A,z))return;return 1})(_=U)||y||(Mt(hi,b),y=!0),so(function(){return!v&&l.next&&l.next(O)}))},function(O){a=!1,["DatabaseClosedError","AbortError"].includes(O==null?void 0:O.name)||v||so(function(){v||l.error&&l.error(O)})}))};return setTimeout(E,0),k});return o.hasValue=function(){return a},o.getValue=function(){return e},o}var hr=wt;function Co(n){var e=Ft;try{Ft=!0,Mt.storagemutated.fire(n),So(n,!0)}finally{Ft=e}}j(hr,s(s({},ar),{delete:function(n){return new hr(n,{addons:[]}).delete()},exists:function(n){return new hr(n,{addons:[]}).open().then(function(e){return e.close(),!0}).catch("NoSuchDatabaseError",function(){return!1})},getDatabaseNames:function(n){try{return e=hr.dependencies,a=e.indexedDB,e=e.IDBKeyRange,(_o(a)?Promise.resolve(a.databases()).then(function(o){return o.map(function(l){return l.name}).filter(function(l){return l!==ea})}):bo(a,e).toCollection().primaryKeys()).then(n)}catch{return re(new tn.MissingAPI)}var e,a},defineClass:function(){return function(n){p(this,n)}},ignoreTransaction:function(n){return rn.trans?fr(rn.transless,n):n()},vip:xo,async:function(n){return function(){try{var e=jo(n.apply(this,arguments));return e&&typeof e.then=="function"?e:X.resolve(e)}catch(a){return re(a)}}},spawn:function(n,e,a){try{var o=jo(n.apply(a,e||[]));return o&&typeof o.then=="function"?o:X.resolve(o)}catch(l){return re(l)}},currentTransaction:{get:function(){return rn.trans||null}},waitFor:function(n,e){return e=X.resolve(typeof n=="function"?hr.ignoreTransaction(n):n).timeout(e||6e4),rn.trans?rn.trans.waitFor(e):e},Promise:X,debug:{get:function(){return Fe},set:function(n){oi(n)}},derive:$,extend:p,props:j,override:en,Events:fi,on:Mt,liveQuery:Vl,extendObservabilitySet:ha,getByKeyPath:H,setByKeyPath:Z,delByKeyPath:function(n,e){typeof e=="string"?Z(n,e,void 0):"length"in e&&[].map.call(e,function(a){Z(n,a,void 0)})},shallowClone:Pn,deepClone:Un,getObjectDiff:To,cmp:jn,asap:ln,minKey:-1/0,addons:[],connections:Mr,errnames:ve,dependencies:ya,cache:dr,semVer:"4.2.0",version:"4.2.0".split(".").map(function(n){return parseInt(n)}).reduce(function(n,e,a){return n+e/Math.pow(10,2*a)})})),hr.maxKey=mi(hr.dependencies.IDBKeyRange),typeof dispatchEvent<"u"&&typeof addEventListener<"u"&&(Mt(hi,function(n){Ft||(n=new CustomEvent(ho,{detail:n}),Ft=!0,dispatchEvent(n),Ft=!1)}),addEventListener(ho,function(n){n=n.detail,Ft||Co(n)}));var Lr,Ft=!1,Hl=function(){};return typeof BroadcastChannel<"u"&&((Hl=function(){(Lr=new BroadcastChannel(ho)).onmessage=function(n){return n.data&&Co(n.data)}})(),typeof Lr.unref=="function"&&Lr.unref(),Mt(hi,function(n){Ft||Lr.postMessage(n)})),typeof addEventListener<"u"&&(addEventListener("pagehide",function(n){if(!wt.disableBfCache&&n.persisted){Fe&&console.debug("Dexie: handling persisted pagehide"),Lr!=null&&Lr.close();for(var e=0,a=Mr;e<a.length;e++)a[e].close({disableAutoOpen:!1})}}),addEventListener("pageshow",function(n){!wt.disableBfCache&&n.persisted&&(Fe&&console.debug("Dexie: handling persisted pageshow"),Hl(),Co({all:new be(-1/0,[[]])}))})),X.rejectionMapper=function(n,e){return!n||n instanceof an||n instanceof TypeError||n instanceof SyntaxError||!n.name||!ai[n.name]?n:(e=new ai[n.name](e||n.message,n),"stack"in n&&C(e,"stack",{get:function(){return this.inner.stack}}),e)},oi(Fe),s(wt,Object.freeze({__proto__:null,Dexie:wt,liveQuery:Vl,Entity:_l,cmp:jn,PropModification:ci,replacePrefix:function(n,e){return new ci({replacePrefix:[n,e]})},add:function(n){return new ci({add:n})},remove:function(n){return new ci({remove:n})},default:wt,RangeSet:be,mergeRanges:yi,rangesOverlap:Dl}),{default:wt}),wt})})(Di)),Di.exports}var Lu=Nu();const Wa=Fu(Lu),Zs=Symbol.for("Dexie"),Ri=globalThis[Zs]||(globalThis[Zs]=Wa);if(Wa.semVer!==Ri.semVer)throw new Error(`Two different versions of Dexie loaded in the same app: ${Wa.semVer} and ${Ri.semVer}`);const{liveQuery:wd,mergeRanges:gd,rangesOverlap:bd,RangeSet:_d,cmp:xd,Entity:kd,PropModification:Pd,replacePrefix:Ud,add:Ed,remove:Sd,DexieYProvider:jd}=Ri,Ee=new Ri("tm-short-info-db");Ee.version(9).stores({attributes:"&id, lastUpdated",clubs:"&id, lastUpdated",coaches:"&id, lastUpdated",competitions:"&id, lastUpdated",players:"&id, lastUpdated",referees:"&id, lastUpdated"});async function Ku(t,r){const s=await Ee.attributes.get({id:"attributes"});if(!s||Date.now()-s.lastUpdated>864e5||s.cacheKey!==r){const{data:c,error:d}=await t.GET("/attributes");return d||!(c!=null&&c.data)?Promise.reject(new Error("Failed to fetch data for attributes")):(await Ee.attributes.clear(),await Ee.attributes.put({...c.data,id:"attributes",cacheKey:r,lastUpdated:Date.now(),lastAccessed:Date.now()}),c.data)}const f=await Ee.attributes.get("attributes");return f||Promise.reject(new Error("No cached data found for attributes"))}const Xa=6e5,Zr={clubs:{getTable:()=>Ee.clubs,endpoint:"/clubs",mapFunction:t=>{var s,f,c,d,m,p;const r=t.relativeUrl||"",i=r.split("/")[1]||"verein";return{id:t.id,name:t.name,shortName:((s=t.baseDetails)==null?void 0:s.shortName)||t.name,abbreviation:((f=t.baseDetails)==null?void 0:f.abbreviation)||"",clubCode:((c=t.preferences)==null?void 0:c.clubCode)||"",countryId:((d=t.baseDetails)==null?void 0:d.countryId)||0,isNationalTeam:((m=t.baseDetails)==null?void 0:m.isNationalTeam)||!1,crestUrl:((p=t.crestUrl)==null?void 0:p.replace("medium","profil"))||"",historical:t.historical||{},relativeUrl:r,slug:i}},errorMessage:"Failed to fetch clubs data"},coaches:{getTable:()=>Ee.coaches,endpoint:"/coaches",mapFunction:t=>({id:t.id,name:t.name,relativeUrl:t.relativeUrl??""}),errorMessage:"Failed to fetch coaches data"},players:{getTable:()=>Ee.players,endpoint:"/players",mapFunction:t=>t,errorMessage:"Failed to fetch players data"},referees:{getTable:()=>Ee.referees,endpoint:"/referees",mapFunction:t=>({id:t.id,name:t.name,gender:t.gender,portraitUrl:t.portraitUrl??"",dateOfBirth:t.dateOfBirth??"",nationalities:t.nationalities}),errorMessage:"Failed to fetch referees data"},competitions:{getTable:()=>Ee.competitions,endpoint:"/competitions",mapFunction:t=>{var r,i;return{id:t.id,name:t.name,currentSeasonId:t.currentSeasonId??0,shortName:t.baseDetails.shortName??t.name,countryId:t.originDetails.countryId??0,logoUrl:((r=t.logoUrl)==null?void 0:r.replace("medium","mediumsmall"))??"",confederationId:t.originDetails.confederationId??0,totalMarketValue:t.totalMarketValue,relativeUrl:t.relativeUrl??"",typeId:t.typeId??0,isTournament:t.baseDetails.isTournament??!1,slug:((i=t.relativeUrl)==null?void 0:i.split("/")[1])||"wettbewerb",historical:{names:t.historical.names??[],logos:(t.historical.images??[]).map(s=>{var f;return{...s,url:((f=s.url)==null?void 0:f.replace("medium","mediumsmall"))??""}})}}},errorMessage:"Failed to fetch competitions data"}};function qu(t,r){const i=Date.now();return{...t,lastUpdated:i,cacheKey:r}}function Vu(t,r,i){let s=[];const f=r.filter(m=>m!=="0");if(!t)return{notCachedIds:f};const c=Date.now();if(t.length===f.length){for(let m=0;m<f.length;m++){const p=t[m],w=f[m];(!p||p.cacheKey!==i||c-p.lastUpdated>Xa)&&s.push(w)}return{notCachedIds:s}}const d=new Map;for(const m of t)m&&d.set(m.id,m);for(const m of f){const p=d.get(m);(!p||p.cacheKey!==i||c-p.lastUpdated>Xa)&&s.push(m)}return{notCachedIds:s}}function $i(t,r){if(r<=0)return[];const i=[];for(let s=0;s<t.length;s+=r)i.push(t.slice(s,s+r));return i}const Hu=async(t,r,i)=>{const{data:s,error:f}=await i.GET(t,{params:{query:{"ids[]":r}}});if(!(s!=null&&s.data)||f)throw new Error(`Failed to fetch ${t.replace("/","")}`);return s.data},Gu=250,nl=100,Mi=100;async function ni(t,r,i,s){const{getTable:f,endpoint:c,mapFunction:d,errorMessage:m}=s,p=f(),w=[...new Set(t)];let I=[];if(w.length>nl){const j=$i(w,nl);I=(await Promise.all(j.map(C=>p.bulkGet(C)))).flat()}else I=await p.bulkGet(w);const{notCachedIds:T}=Vu(I,w,i);if(T.length){const j=w;if(j.length>Mi){const M=$i(j,Mi);await Promise.all(M.map(W=>p.where("id").anyOf(W).delete()))}else await p.where("id").anyOf(j).delete();const C=(await Promise.all($i(j,Gu).map(M=>Hu(c,M,r)))).flat().map(M=>qu(d(M),i)),$=new Set(j),R=I.filter(M=>!!M&&!$.has(M.id));if(C.length>Mi){const M=$i(C,Mi);await Promise.all(M.map(W=>p.bulkPut(W)))}else await p.bulkPut(C);return[...R,...C]}return I.filter(Boolean)}async function Yu(t,r,i,s,f,c,d){return Promise.all([ni(t,c,d,Zr.clubs),ni(i,c,d,Zr.coaches),ni(r,c,d,Zr.competitions),ni(s,c,d,Zr.players),ni(f,c,d,Zr.referees),Ku(c,d)])}function qe(t){return t.reduce((r,i)=>(r[String(i.id)]=i,r),{})}function Wu(t,r,i,s,f,c){const d=c||{};return{clubs:qe(t),coaches:qe(r),competitions:qe(i),positions:qe(d.positions||[]),injuries:qe(d.injuries||[]),absences:qe(d.absences||[]),competitionTypes:qe(d.competitionTypes||[]),competitionGroups:qe(d.competitionGroups||[]),countries:qe(d.countries||[]),confederations:qe(d.confederations||[]),players:qe(s),referees:qe(f)}}const Xu={maxAge:Xa,maxEntitiesPerTable:1e4,cleanupInterval:1800*1e3};async function Ju(t,r){const s=Date.now()-r.maxAge,c=(await t.where("lastUpdated").below(s).toArray()).length;c>0&&await t.where("lastUpdated").below(s).delete();const d=await t.count();let m=0;if(d>r.maxEntitiesPerTable){const p=d-r.maxEntitiesPerTable,w=await t.orderBy("lastUpdated").limit(p).toArray();if(w.length>0){const I=w.map(T=>T.id);await t.where("id").anyOf(I).delete(),m=w.length}}return{expired:c,excess:m}}async function Qu(t={}){const r=performance.now(),i={...Xu,...t},s=[{table:Ee.clubs,name:"clubs"},{table:Ee.players,name:"players"},{table:Ee.competitions,name:"competitions"},{table:Ee.coaches,name:"coaches"},{table:Ee.referees,name:"referees"}],f={};let c=0,d=0;for(const{table:p,name:w}of s)try{const I=await Ju(p,i);f[w]=I,c+=I.expired,d+=I.excess}catch(I){console.warn(`Failed to cleanup ${w} table:`,I),f[w]={expired:0,excess:0}}const m=performance.now()-r;return(c>0||d>0)&&console.log(`Entity cache cleanup completed in ${m}ms:`,{totalExpired:c,totalExcess:d,tableResults:f}),{totalExpired:c,totalExcess:d,tableResults:f,duration:m}}const Zu={maxAge:6e5,maxEntitiesPerTable:1e4,cleanupInterval:18e5,enableLRU:!0};function nc(t){return Zu}let el=0,Fi=null;async function ec(t=!1){if(Fi)return!1;const r=nc(),i=Date.now(),s=i-el;if(!t&&s<r.cleanupInterval)return!1;try{return Fi=Qu(r),await Fi,el=i,!0}catch(f){return console.warn("Automatic cache cleanup failed:",f),!1}finally{Fi=null}}async function tc({clubs:t=[],competitions:r=[],coaches:i=[],players:s=[],referees:f=[]},c){const d=performance.now(),m=Js({useLocale:!0,signal:c}),p=document.body.getAttribute("data-db-name")||"";try{const[w,I,T,j,D,C]=await Yu(t,r,i,s,f,m,p),$=Wu(w,I,T,j,D,C),R=performance.now()-d;return console.log("Resolved entities in",R,"ms"),ec().catch(M=>{console.warn("Cache cleanup failed:",M)}),$}catch(w){throw console.error("Failed to resolve entities:",w),new Error(`Entity resolution failed: ${w instanceof Error?w.message:"Unknown error"}`)}}const rc=[{digitLang:"de",domain:"at",formatPlan:"v n c",lang:"de-AT",wishCurrency:"€"},{digitLang:"nl",domain:"be",formatPlan:"v n c",lang:"nl-BE",wishCurrency:"€"},{digitLang:"br",domain:"br",formatPlan:"c v n",lang:"pt-BR",wishCurrency:"€"},{digitLang:"de",domain:"ch",formatPlan:"v n c",lang:"de-CH",wishCurrency:"€"},{digitLang:"de",domain:"de",formatPlan:"v n c",lang:"de-DE",wishCurrency:"€"},{digitLang:"es",domain:"es",formatPlan:"v n c",lang:"es-ES",wishCurrency:"€"},{digitLang:"fr",domain:"fr",formatPlan:"v n c",lang:"fr-FR",wishCurrency:"€"},{digitLang:"en",domain:"in",formatPlan:"cvn",lang:"en-GB",wishCurrency:"₹"},{digitLang:"it",domain:"it",formatPlan:"v n c",lang:"it-IT",wishCurrency:"€"},{digitLang:"nl",domain:"nl",formatPlan:"v n c",lang:"nl-NL",wishCurrency:"€"},{digitLang:"es",domain:"pe",formatPlan:"v n c",lang:"pe-ES",wishCurrency:"€"},{digitLang:"pl",domain:"pl",formatPlan:"v n c",lang:"pl-PL",wishCurrency:"€"},{digitLang:"pt",domain:"pt",formatPlan:"v n c",lang:"pt-PT",wishCurrency:"€"},{digitLang:"ru",domain:"ru",formatPlan:"v n c",lang:"ru-RU",wishCurrency:"€"},{digitLang:"ru",domain:"world",formatPlan:"v n c",lang:"ru-RU",wishCurrency:"€"},{digitLang:"tr",domain:"tr",formatPlan:"v n c",lang:"tr-TR",wishCurrency:"€"},{digitLang:"en",domain:"uk",formatPlan:"cvn",lang:"en-GB",wishCurrency:"€"},{digitLang:"en",domain:"us",formatPlan:"cvn",lang:"en-US",wishCurrency:"€"},{digitLang:"de",domain:"tv",formatPlan:"v n c",lang:"de-DE",wishCurrency:"€"},{digitLang:"id",domain:"id",formatPlan:"cvn",lang:"id-ID",wishCurrency:"Rp"},{digitLang:"en",domain:"jp",formatPlan:"v n c",lang:"ja-JP",wishCurrency:"€"},{digitLang:"es",domain:"co",formatPlan:"v n c",lang:"es-CO",wishCurrency:"€"},{digitLang:"es",domain:"mx",formatPlan:"v n c",lang:"es-MX",wishCurrency:"€"},{digitLang:"es",domain:"ar",formatPlan:"v n c",lang:"es-AR",wishCurrency:"€"},{digitLang:"es",domain:"my",formatPlan:"v n c",lang:"en-GB",wishCurrency:"€"},{digitLang:"en",domain:"za",formatPlan:"v n c",lang:"en-GB",wishCurrency:"€"},{digitLang:"en",domain:"com",formatPlan:"cvn",lang:"en-GB",wishCurrency:"€"},{digitLang:"en",domain:"kr",formatPlan:"v n c",lang:"en-GB",wishCurrency:"€"},{digitLang:"en",domain:"gr",formatPlan:"v n c",lang:"el-GR",wishCurrency:"€"},{digitLang:"ro",domain:"ro",formatPlan:"v n c",lang:"ro-RO",wishCurrency:"€"}];class Ja{constructor(){ae(this,"rates",{"€":1,"£":.9,$:1.1,"₹":80,Rp:17381.64});ae(this,"countriesConfigMap",[{digitFormats:["mil","mill.","mil mill."],lang:"es",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["Tsd.","Mio.","Mrd."],lang:"de",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["dzd.","mln.","mld."],lang:"nl",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["mil","mi.","bilhões"],lang:"br",decimalSeparator:".",showTwoDecimals:!0},{digitFormats:["mil","M.","Mil milhões"],lang:"pt",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["K","mio","mrd."],lang:"fr",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["Rb.","Jt.","Mlyr."],lang:"id",decimalSeparator:".",showTwoDecimals:!0},{digitFormats:["mila","mln","mld"],lang:"it",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["tys.","mln","mld"],lang:"pl",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["тыс","млн","Млрд"],lang:"ru",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["bin","mil.","milyar"],lang:"tr",decimalSeparator:".",showTwoDecimals:!0},{digitFormats:["k","mil.","bn"],lang:"za",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["k","m","bn"],lang:"en",decimalSeparator:".",showTwoDecimals:!0},{digitFormats:["k","mil.","bn"],lang:"kr",decimalSeparator:".",showTwoDecimals:!0},{digitFormats:["χιλ.","εκατ.","δισ."],lang:"gr",decimalSeparator:",",showTwoDecimals:!0},{digitFormats:["mii","mil.","mld."],lang:"ro",decimalSeparator:",",showTwoDecimals:!0}]);ae(this,"configValuesMap",[{digitFormat:"",max:999,min:0},{digitFormat:"k",max:999999,min:1e3},{digitFormat:"m",max:999999999,min:1e6},{digitFormat:"bn",max:999999999999999,min:1e9}])}formatIndianValue(r){return r=r*this.rates["₹"],r>1e7?(r=r/1e7,"₹"+r+" Cr"):(r=r/1e5,r===100?"₹1 Cr":"₹"+r+" L")}formatIndonesianValue(r){return r=r*this.rates.Rp,r>1e9?(r=r/1e9,r.toFixed(2).replace(".",",")+"Mlyr."):r<1e9?(r=r/1e6,r.toFixed(2).replace(".",",")+"Jt."):r.toFixed(2).replace(".",",")}format(r,i="en"){if(i=="in")return this.formatIndianValue(r);if(i=="id")return this.formatIndonesianValue(r);const s=rc.find(d=>i===d.domain)||{digitLang:"en",formatPlan:"cvn",wishCurrency:"$"},f=this.countriesConfigMap.find(d=>d.lang===i)||this.countriesConfigMap.find(d=>d.lang===s.digitLang);if(f)for(let d=1;d<this.configValuesMap.length;d++)this.configValuesMap[d].digitFormat=f.digitFormats[d-1];const c=this.configValuesMap.find(d=>r>=d.min&&r<=d.max);if(c){const d=c.min||1,m=(f==null?void 0:f.decimalSeparator)||".",p=+(r*this.rates[s.wishCurrency]/d).toFixed(2),I=(f==null?void 0:f.showTwoDecimals)&&(c.digitFormat===f.digitFormats[1]||c.digitFormat===f.digitFormats[2])?p.toFixed(2).replace(".",m):Math.round(p).toString().replace(".",m);return s.formatPlan.replace("c",s.wishCurrency).replace("v",I).replace("n",c.digitFormat)}return""}}Of();const ic={hash:"svelte-1r2dsxy",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */.content-box-headline.svelte-1r2dsxy {background-color:#00193f;color:white;display:block;font-family:'Oswald-VF', 'Oswald-fallback', sans-serif;font-size:1rem;font-variation-settings:"wght" 400;font-weight:initial;line-height:1.625rem;margin:0;padding:0 0.5rem !important;scroll-margin-top:5rem;text-transform:uppercase;}`};function tl(t,r){se(r,!1),$e(t,ic);let i=pn(r,"elementType",12,"div"),s=pn(r,"headline",12,"");var f={get elementType(){return i()},set elementType(m){i(m),mn()},get headline(){return s()},set headline(m){s(m),mn()}},c=Oe(),d=ee(c);return hu(d,i,!1,(m,p)=>{pu(m,0,"content-box-headline svelte-1r2dsxy");var w=Pt();qn(()=>Bn(w,s())),on(p,w)}),on(t,c),le(f)}he(tl,{elementType:{},headline:{}},[],[],!0);function rl(t,r){se(r,!1);let i=pn(r,"isMobile",12,!0),s,f;const c=()=>{f&&f.matches?i(!1):i(!(s!=null&&s.matches))},d=()=>{s==null||s.removeEventListener("change",c),f==null||f.removeEventListener("change",c)};$s(()=>(s=window.matchMedia("(min-width: 769px)"),f=window.matchMedia("print"),c(),s.addEventListener("change",c),f.addEventListener("change",c),d));var p={get isMobile(){return i()},set isMobile(w){i(w),mn()}};return xu(),le(p)}he(rl,{isMobile:{}},[],[],!0);var ac=Mn('<div class="svelte-vwdx24"></div>');const oc={hash:"svelte-vwdx24",code:`/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
@keyframes svelte-vwdx24-sk-pulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 0.8;
  }
  100% {
    opacity: 0.6;
  }
}div.svelte-vwdx24 {
  animation: svelte-vwdx24-sk-pulse 1.5s infinite;background-color:#e9e9e9;border-radius:50%;}`};function Bi(t,r){se(r,!1),$e(t,oc);let i=pn(r,"size",12,"100%");var s={get size(){return i()},set size(c){i(c),mn()}},f=ac();return qn(()=>Fs(f,`height: ${i()??""}; width: ${i()??""};`)),on(t,f),le(s)}he(Bi,{size:{}},[],[],!0);var sc=Mn('<div class="svelte-py9x6n"></div>');const lc={hash:"svelte-py9x6n",code:`/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
@keyframes svelte-py9x6n-sk-pulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 0.8;
  }
  100% {
    opacity: 0.6;
  }
}div.svelte-py9x6n {
  animation: svelte-py9x6n-sk-pulse 1.5s infinite;background-color:#e9e9e9;}`};function Yn(t,r){se(r,!1),$e(t,lc);let i=pn(r,"height",12,"100%"),s=pn(r,"width",12,"100%");var f={get height(){return i()},set height(d){i(d),mn()},get width(){return s()},set width(d){s(d),mn()}},c=sc();return qn(()=>Fs(c,`height: ${i()??""}; width: ${s()??""};`)),on(t,c),le(f)}he(Yn,{height:{},width:{}},[],[],!0);var fc=Mn('<div class="sk-row svelte-lu0fk6"><div class="sk-row__left svelte-lu0fk6"><!></div> <div class="sk-row__center svelte-lu0fk6"><!> <div class="svelte-lu0fk6"><!> <!></div></div> <div class="sk-row__right svelte-lu0fk6"><!></div></div>'),uc=Mn('<div><!> <!> <div class="sk-row__last svelte-lu0fk6"><!></div></div>');const cc={hash:"svelte-lu0fk6",code:`/* stylelint-disable */
/* stylelint-enable */.sk-row.svelte-lu0fk6 {display:grid;grid-template-columns:3.125rem auto 6.25rem;height:45px;padding:0 0.3125rem 0 0.25rem;}.sk-row__left.svelte-lu0fk6 {align-items:center;display:flex;justify-content:center;}.sk-row__center.svelte-lu0fk6 {align-items:center;display:flex;gap:0.875rem;}.sk-row__center.svelte-lu0fk6 > div:where(.svelte-lu0fk6) {display:flex;flex-direction:column;gap:0.25rem;}.sk-row__right.svelte-lu0fk6 {align-items:center;display:flex;justify-content:flex-end;}.sk-row__last.svelte-lu0fk6 {align-items:center;display:flex;height:28px;justify-content:flex-end;padding-right:0.25rem;}`};function il(t,r){se(r,!1),$e(t,cc);let i=pn(r,"rowCount",12,9),s=pn(r,"logoSize",12,"30px");var f={get rowCount(){return i()},set rowCount(I){i(I),mn()},get logoSize(){return s()},set logoSize(I){s(I),mn()}},c=uc(),d=vn(c);Yn(d,{height:"26px"});var m=wn(d,2);Va(m,1,()=>(Na(i()),nr(()=>Array(i()))),qa,(I,T)=>{var j=fc(),D=vn(j),C=vn(D);Yn(C,{height:"12px",width:"30px"}),hn(D);var $=wn(D,2),R=vn($);Bi(R,{get size(){return s()}});var M=wn(R,2),W=vn(M);Yn(W,{height:"12px",width:"80px"});var en=wn(W,2);Yn(en,{height:"12px",width:"50px"}),hn(M),hn($);var q=wn($,2),ln=vn(q);Yn(ln,{height:"12px",width:"55px"}),hn(q),hn(j),on(I,j)});var p=wn(m,2),w=vn(p);return Yn(w,{height:"12px",width:"50%"}),hn(p),hn(c),on(t,c),le(f)}he(il,{rowCount:{},logoSize:{}},[],[],!0);var dc=Mn("<!> <!> <!>",1),hc=Mn("<!> <!> <!>",1),mc=Mn('<div class="sk-row svelte-16tqe68"><div class="sk-row__left svelte-16tqe68"><!></div> <div class="sk-row__left svelte-16tqe68"><!></div> <div class="sk-row__center svelte-16tqe68"><!></div> <div class="sk-row__center svelte-16tqe68"><!></div> <div class="sk-row__right svelte-16tqe68"><!></div> <div class="sk-row__right svelte-16tqe68"><!></div> <div></div></div>'),vc=Mn('<div><!> <!> <div class="sk-row__last svelte-16tqe68"><!></div></div>');const pc={hash:"svelte-16tqe68",code:`/* stylelint-disable */
/* stylelint-enable */.sk-row.svelte-16tqe68 {display:grid;grid-template-columns:3.5625rem 5.5rem 1fr 1fr 5.4375rem 4.6875rem 1.0625rem;height:28px;padding:0.5rem 0.25rem;}.sk-row__left.svelte-16tqe68 {display:flex;justify-content:center;}.sk-row__center.svelte-16tqe68 {align-items:center;display:flex;gap:0.5rem;}.sk-row__right.svelte-16tqe68 {display:flex;justify-content:flex-end;}.sk-row__last.svelte-16tqe68 {align-items:center;display:flex;height:28px;justify-content:flex-end;padding-right:0.25rem;}`};function al(t,r){se(r,!1),$e(t,pc);let i=pn(r,"rowCount",12,9);var s={get rowCount(){return i()},set rowCount(w){i(w),mn()}},f=vc(),c=vn(f);Yn(c,{height:"26px"});var d=wn(c,2);Va(d,1,()=>(Na(i()),nr(()=>Array(i()))),qa,(w,I,T)=>{var j=mc(),D=vn(j),C=vn(D);Yn(C,{height:"12px",width:"40px"}),hn(D);var $=wn(D,2),R=vn($);{var M=nn=>{Yn(nn,{height:"12px",width:"30px"})},W=nn=>{Yn(nn,{height:"12px",width:"50px"})};Fn(R,nn=>{T===0?nn(M):nn(W,!1)})}hn($);var en=wn($,2),q=vn(en);{var ln=nn=>{Yn(nn,{height:"12px",width:"140px"})},H=nn=>{var Nn=dc(),Xn=ee(Nn);Bi(Xn,{size:"15px"});var Q=wn(Xn,2);Yn(Q,{height:"11px",width:"14px"});var an=wn(Q,2);Yn(an,{height:"12px",width:"60px"}),on(nn,Nn)};Fn(q,nn=>{T===0?nn(ln):nn(H,!1)})}hn(en);var Z=wn(en,2),Pn=vn(Z);{var Wn=nn=>{Yn(nn,{height:"12px",width:"140px"})},Dn=nn=>{var Nn=hc(),Xn=ee(Nn);Bi(Xn,{size:"15px"});var Q=wn(Xn,2);Yn(Q,{height:"11px",width:"14px"});var an=wn(Q,2);Yn(an,{height:"12px",width:"60px"}),on(nn,Nn)};Fn(Pn,nn=>{T===0?nn(Wn):nn(Dn,!1)})}hn(Z);var Cn=wn(Z,2),zn=vn(Cn);{var Un=nn=>{Yn(nn,{height:"12px",width:"20px"})},bn=nn=>{Yn(nn,{height:"12px",width:"60px"})};Fn(zn,nn=>{T===0?nn(Un):nn(bn,!1)})}hn(Cn);var sn=wn(Cn,2),te=vn(sn);{var Rn=nn=>{Yn(nn,{height:"12px",width:"40px"})},En=nn=>{Yn(nn,{height:"12px",width:"60px"})};Fn(te,nn=>{T===0?nn(Rn):nn(En,!1)})}hn(sn),Oa(2),hn(j),on(w,j)});var m=wn(d,2),p=vn(m);return Yn(p,{height:"12px",width:"200px"}),hn(m),hn(f),on(t,f),le(s)}he(al,{rowCount:{}},[],[],!0);function ol(t,r){se(r,!0);let i=pn(r,"isMobile",7),s=ye(9);var f={get isMobile(){return i()},set isMobile(w){i(w),mn()}},c=Oe(),d=ee(c);{var m=w=>{il(w,{rowCount:V(s),logoSize:"37px"})},p=w=>{al(w,{rowCount:V(s)})};Fn(d,w=>{i()?w(m):w(p,!1)})}return on(t,c),le(f)}he(ol,{isMobile:{}},[],[],!0);var yc=Mn('<section class="svelte-89darn"><div class="svelte-89darn"> </div> <div class="svelte-89darn"> </div> <div class="svelte-89darn"> </div> <div class="svelte-89darn"> </div> <div class="svelte-89darn"> </div> <div class="svelte-89darn"> </div> <span class="svelte-89darn"></span></section>');const wc={hash:"svelte-89darn",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}section.svelte-89darn {background-color:#f2f2f2;display:none;font-family:'SourceSansPro-VF', sans-serif;font-size:0.75rem;font-style:normal;font-variation-settings:"wght" 700;font-weight:initial;grid-template:"season emblem club fee" 1.375rem "season emblem date fee" 1.4375rem/3.125rem 2.8125rem auto 6.25rem;justify-content:center;}
@media print, (min-width: 769px) {section.svelte-89darn {display:grid;grid-template:"season date clubold clubnew marketvalue fee link" minmax(1.75rem, auto)/3.5625rem 5.5rem 1fr 1fr 5.4375rem 4.6875rem 1.0625rem;}
}section.svelte-89darn > div:where(.svelte-89darn) {align-items:center;border-bottom:0.0625rem solid #e4e4e4;border-collapse:collapse;border-right:0.0625rem solid #e4e4e4;color:#333333;display:flex;justify-content:center;padding:0 0.25rem;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(1) {grid-area:season;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(2) {grid-area:date;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(3) {grid-area:clubold;justify-content:flex-start;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(4) {grid-area:clubnew;justify-content:flex-start;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(5) {grid-area:marketvalue;justify-content:flex-end;}section.svelte-89darn > div:where(.svelte-89darn):nth-child(6) {grid-area:fee;justify-content:flex-end;}section.svelte-89darn > span:where(.svelte-89darn) {border-bottom:0.0625rem solid #e4e4e4;grid-area:link;}`};function sl(t,r){se(r,!0),$e(t,wc);let i=pn(r,"translations",7);var s={get translations(){return i()},set translations(M){i(M),mn()}},f=yc(),c=vn(f),d=vn(c,!0);hn(c);var m=wn(c,2),p=vn(m,!0);hn(m);var w=wn(m,2),I=vn(w,!0);hn(w);var T=wn(w,2),j=vn(T,!0);hn(T);var D=wn(T,2),C=vn(D,!0);hn(D);var $=wn(D,2),R=vn($,!0);return hn($),Oa(2),hn(f),qn(()=>{var M,W,en,q,ln,H;Bn(d,((M=i())==null?void 0:M.season)??""),Bn(p,((W=i())==null?void 0:W.date)??""),Bn(I,((en=i())==null?void 0:en.clubFrom)??""),Bn(j,((q=i())==null?void 0:q.clubTo)??""),Bn(C,((ln=i())==null?void 0:ln.marketValue)??""),Bn(R,((H=i())==null?void 0:H.transferFee)??"")}),on(t,f),le(s)}he(sl,{translations:{}},[],[],!0);function Ur(t){const r=t.target;return r.src="https://tmsi.akamaized.net/wappen/default_club.png",r.src}function ll(t,r){const i={...JSON.parse(JSON.stringify(r)),name:r.name===""?"":r.name,shortName:r.shortName===""?r.name:r.shortName,abbreviation:r.abbreviation===""?"":r.abbreviation},s=gc(t,r,i);return{...i,name:s.name,shortName:s.shortName,abbreviation:s.abbreviation,crestUrl:bc(t,r,i.crestUrl)}}function gc(t,r,i){var c,d;(c=r.historical.names)==null||c.map(m=>{m.seasonId===0&&(m.seasonId=3e3)});const s=(d=r.historical.names)==null?void 0:d.filter(m=>m.seasonId!==void 0&&m.seasonId!==0&&m.seasonId>=t);if(!s||s.length===0)return{name:r.name===""?"":r.name,shortName:r.shortName===""?"":r.shortName,abbreviation:r.abbreviation===""?"":r.abbreviation};const f=s[s.length-1];return f&&(i.name=f.name??"",i.shortName=f.shortName?f.shortName:i.name,i.abbreviation=f.abbreviation?f.abbreviation:""),{name:i.name,shortName:i.shortName,abbreviation:i.abbreviation}}function bc(t,r,i){var c,d,m;const s=(c=r.historical.images)==null?void 0:c.map(p=>({...p,...p.seasonId===0?{seasonId:3e3}:{seasonId:p.seasonId}})),f=s==null?void 0:s.filter(p=>p.seasonId!==void 0&&p.seasonId!==0&&p.seasonId>=t);return f&&f.length?((m=(d=f[f.length-1])==null?void 0:d.url)==null?void 0:m.replace("big","medium"))??i:i}var _c=Mn('<img class="flag svelte-gjxr9f"/>'),xc=Mn('<a class="svelte-gjxr9f"> </a>'),kc=Mn('<span class="svelte-gjxr9f"> </span>'),Pc=Mn('<a class="hide-mobile svelte-gjxr9f"><img class="logo svelte-gjxr9f" height="15" width="15"/></a> <!> <!>',1);const Uc={hash:"svelte-gjxr9f",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}img.svelte-gjxr9f {box-sizing:border-box;}a.svelte-gjxr9f {box-sizing:border-box;color:#1d75a3;font-variation-settings:"wght" 700;text-decoration:none;}a.svelte-gjxr9f:hover {text-decoration:underline;}span.svelte-gjxr9f {font-variation-settings:"wght" 700;}.logo.svelte-gjxr9f {height:1.875rem;max-height:19px;max-width:15px;width:auto;}
@media print, (min-width: 769px) {.logo.svelte-gjxr9f {height:auto;}
}.hide-mobile.svelte-gjxr9f {display:none;}
@media print, (min-width: 769px) {.hide-mobile.svelte-gjxr9f {display:initial;}
}.flag.svelte-gjxr9f {border:0.0625rem solid #57585a;height:auto;margin:0 0.25rem 0 0;width:0.875rem;}
@media print, (min-width: 769px) {.flag.svelte-gjxr9f {margin:0 0.5rem;}
}`};function Qa(t,r){se(r,!0),$e(t,Uc);let i=pn(r,"club",7),s=pn(r,"country",7),f=pn(r,"seasonId",7);const c=(R,M)=>M?`/${R.slug}/transfers/verein/${R.id}/saison_id/${M}`:`/${R.slug}/transfers/verein/${R.id}`;let d=ne(()=>c(i(),f()));var m={get club(){return i()},set club(R){i(R),mn()},get country(){return s()},set country(R){s(R),mn()},get seasonId(){return f()},set seasonId(R){f(R),mn()}},p=Pc(),w=ee(p),I=vn(w);hn(w);var T=wn(w,2);{var j=R=>{var M=_c();qn(()=>{ge(M,"src",s().flagUrl),ge(M,"alt",s().name)}),on(R,M)};Fn(T,R=>{s()&&R(j)})}var D=wn(T,2);{var C=R=>{var M=xc(),W=vn(M,!0);hn(M),qn(()=>{ge(M,"href",V(d)),ge(M,"title",i().shortName||i().name),Bn(W,i().shortName||i().name)}),on(R,M)},$=R=>{var M=kc(),W=vn(M,!0);hn(M),qn(()=>Bn(W,i().shortName||i().name)),on(R,M)};Fn(D,R=>{i().slug!=="verein"?R(C):R($,!1)})}return qn(()=>{ge(w,"href",V(d)),ge(w,"title",i().shortName||i().name),ge(I,"src",i().crestUrl),ge(I,"alt",i().shortName)}),Cs("error",I,function(...R){Ur==null||Ur.apply(this,R)}),As(I),on(t,p),le(m)}he(Qa,{club:{},country:{},seasonId:{}},[],[],!0);function fl(t,r){se(r,!0);let i=pn(r,"date",7),s=pn(r,"locale",7),f=ne(()=>i()?new Intl.DateTimeFormat(s(),{day:"2-digit",month:"2-digit",year:"numeric",timeZone:"Europe/Berlin"}).format(new Date(i())):null);var c={get date(){return i()},set date(w){i(w),mn()},get locale(){return s()},set locale(w){s(w),mn()}},d=Oe(),m=ee(d);{var p=w=>{var I=Pt();qn(()=>Bn(I,V(f))),on(w,I)};Fn(m,w=>{V(f)&&w(p)})}return on(t,d),le(c)}he(fl,{date:{},locale:{}},[],[],!0);var Ec=Mn(" <br/> ",1),Sc=Mn(" <br/> ",1);function Za(t,r){se(r,!0);let i=pn(r,"typeDetails",7),s=pn(r,"fee",7),f=pn(r,"tld",7),c=pn(r,"translations",7);const d=new Ja;var m={get typeDetails(){return i()},set typeDetails(j){i(j),mn()},get fee(){return s()},set fee(j){s(j),mn()},get tld(){return f()},set tld(j){f(j),mn()},get translations(){return c()},set translations(j){c(j),mn()}},p=Oe(),w=ee(p);{var I=j=>{var D=Oe(),C=ee(D);{var $=M=>{var W=Pt();qn(en=>Bn(W,en),[()=>{var en;return d.format(((en=s())==null?void 0:en.value)??0,f()??"")}]),on(M,W)},R=M=>{var W=Pt();qn(()=>{var en,q;return Bn(W,((q=(en=s())==null?void 0:en.compact)==null?void 0:q.content)??"-")}),on(M,W)};Fn(C,M=>{var W;(W=s())!=null&&W.value?M($):M(R,!1)})}on(j,D)},T=j=>{var D=Oe(),C=ee(D);{var $=M=>{var W=Oe(),en=ee(W);{var q=H=>{var Z=Ec(),Pn=ee(Z),Wn=wn(Pn,2,!0);qn(Dn=>{Bn(Pn,`${i().feeDescription??""} `),Bn(Wn,Dn)},[()=>{var Dn;return d.format(((Dn=s())==null?void 0:Dn.value)??0,f()??"")}]),on(H,Z)},ln=H=>{var Z=Pt();qn(()=>{var Pn;return Bn(Z,(Pn=c())==null?void 0:Pn.loan)}),on(H,Z)};Fn(en,H=>{var Z;(Z=s())!=null&&Z.value?H(q):H(ln,!1)})}on(M,W)},R=M=>{var W=Oe(),en=ee(W);{var q=ln=>{var H=Oe(),Z=ee(H);{var Pn=Dn=>{var Cn=Sc(),zn=ee(Cn),Un=wn(zn,2,!0);qn(bn=>{Bn(zn,`${i().feeDescription??""} `),Bn(Un,bn)},[()=>{var bn;return d.format(((bn=s())==null?void 0:bn.value)??0,f()??"")}]),on(Dn,Cn)},Wn=Dn=>{var Cn=Pt();qn(()=>Bn(Cn,i().feeDescription)),on(Dn,Cn)};Fn(Z,Dn=>{var Cn;(Cn=s())!=null&&Cn.value?Dn(Pn):Dn(Wn,!1)})}on(ln,H)};Fn(en,ln=>{i().type==="RETURNED_FROM_PREVIOUS_LOAN"&&ln(q)},!0)}on(M,W)};Fn(C,M=>{i().type==="ACTIVE_LOAN_TRANSFER"?M($):M(R,!1)},!0)}on(j,D)};Fn(w,j=>{i().type==="STANDARD"?j(I):j(T,!1)})}return on(t,p),le(m)}he(Za,{typeDetails:{},fee:{},tld:{},translations:{}},[],[],!0);var jc=Mn('<a class="svelte-v9j65n"> </a>'),Tc=Mn('<a class="mobile-grid svelte-v9j65n"><img class="logo svelte-v9j65n"/></a>'),Ic=Mn('<span class="svelte-v9j65n">-</span>'),Oc=Mn('<a class="svelte-v9j65n"><!></a>'),Ac=Mn('<section class="svelte-v9j65n"><div class="svelte-v9j65n"><!></div> <div class="svelte-v9j65n"><!></div> <div class="svelte-v9j65n"><!></div> <div class="svelte-v9j65n"><!></div> <div class="svelte-v9j65n"><!></div> <div class="svelte-v9j65n"><!></div> <a class="svelte-v9j65n"></a></section>');const Cc={hash:"svelte-v9j65n",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}section.svelte-v9j65n {box-sizing:border-box;display:grid;font-family:'SourceSansPro-VF', sans-serif;font-size:0.75rem;font-style:normal;font-variation-settings:"wght" 400;font-weight:initial;grid-auto-columns:1fr;grid-auto-rows:1fr;grid-template:"season emblem club fee" 1.375rem "season emblem date fee" 1.4375rem/3.125rem 2.8125rem 1fr 6.25rem;justify-content:center;margin:0;padding:0;}
@media print, (min-width: 769px) {section.svelte-v9j65n {background-color:white;grid-template:"season date clubold clubnew marketvalue fee link" minmax(1.75rem, auto)/3.5625rem 5.5rem 1fr 1fr 5.4375rem 4.6875rem 1.0625rem;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n) {align-items:center;border-bottom:0.125rem solid #dddddd;border-collapse:collapse;box-sizing:border-box;color:#333333;display:flex;justify-content:center;margin:0;padding:0 0.25rem;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n) {border-bottom:0.0625rem solid #e4e4e4;border-right:0.0625rem solid #e4e4e4;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(1) {background-color:#dddddd;border-bottom:0.125rem solid white;grid-area:season;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(1) {background-color:initial;border-bottom:0.0625rem solid #e4e4e4;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(1) > a:where(.svelte-v9j65n) {color:#1d75a3;font-variation-settings:"wght" 700;text-decoration:none;}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(1) > a:where(.svelte-v9j65n):hover {text-decoration:underline;}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(2) {grid-area:date;justify-content:start;padding-bottom:0.3125rem;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(2) {justify-content:center;padding-bottom:0;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) {align-items:center;grid-area:emblem;height:2.8125rem;justify-content:flex-start;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) {display:grid;grid-area:clubold;grid-template:"rowlogo rowflag rowlink" 100%/0.9375rem 1.875rem auto;height:auto;justify-content:flex-start;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > a:nth-child(1) {grid-area:rowlogo;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > img {grid-area:rowflag;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > a:nth-child(2),
  section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > span {grid-area:rowlink;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > a:where(.svelte-v9j65n),
section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) > span:where(.svelte-v9j65n),
section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(3) img:where(.svelte-v9j65n) {box-sizing:border-box;}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) {border-bottom:none;grid-area:club;justify-content:flex-start;margin-top:0.3125rem;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) {border-bottom:0.0625rem solid #e4e4e4;display:grid;grid-area:clubnew;grid-template:"rowlogo rowflag rowlink" 100%/0.9375rem 1.875rem auto;height:auto;margin-top:0;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) > a:nth-child(1) {grid-area:rowlogo;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) > img {grid-area:rowflag;}
}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) > a:nth-child(2),
  section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(4) > span {grid-area:rowlink;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(5) {display:none;grid-area:marketvalue;justify-content:right;}
@media print, (min-width: 769px) {section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(5) {display:flex;}
}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(6) {grid-area:fee;justify-content:right;}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(6) > a:where(.svelte-v9j65n) {color:#1d75a3;font-variation-settings:"wght" 700;text-decoration:none;}section.svelte-v9j65n > div:where(.svelte-v9j65n):nth-child(6) > a:where(.svelte-v9j65n):hover {text-decoration:underline;}section.svelte-v9j65n > a:where(.svelte-v9j65n) {display:none;}
@media print, (min-width: 769px) {section.svelte-v9j65n > a:where(.svelte-v9j65n) {align-items:flex-end;border-bottom:0.0625rem solid #e4e4e4;display:flex;flex-direction:column;grid-area:link;justify-content:center;padding:0 0.25rem;text-align:right;}
}section.svelte-v9j65n > a:where(.svelte-v9j65n)::after {border-right:0.0625rem solid #00aded;border-top:0.0625rem solid #00aded;box-sizing:border-box;content:"";display:block;grid-area:link;height:0.375rem;margin-right:0.1875rem;transform:rotate(45deg);width:0.375rem;}section.svelte-v9j65n img.logo:where(.svelte-v9j65n) {height:2.3125rem;width:auto;}`};function ul(t,r){se(r,!0),$e(t,Cc);const i=new Ja;let s=pn(r,"tld",7),f=pn(r,"transfer",7),c=pn(r,"locale",7),d=pn(r,"resolvedEntities",7),m=pn(r,"isMobile",7,!1),p=pn(r,"translations",7),w=pn(r,"historical",7,!0),I=ne(()=>{var Q,an,xn,Sn,Vn;return`${(Sn=(xn=d().players[(an=(Q=f())==null?void 0:Q.details)==null?void 0:an.playerId])==null?void 0:xn.relativeUrl)==null?void 0:Sn.replace("/profil/","/transfers/")}/transfer_id/${(Vn=f())==null?void 0:Vn.id}`}),T=ne(()=>w()?ll(f().details.seasonId??0,d().clubs[f().transferSource.clubId]):d().clubs[f().transferSource.clubId]),j=ne(()=>w()?ll(f().details.seasonId??0,d().clubs[f().transferDestination.clubId]):d().clubs[f().transferDestination.clubId]),D=ne(()=>{var Q,an,xn;return((xn=(an=(Q=f())==null?void 0:Q.details)==null?void 0:an.season)==null?void 0:xn.display)??""});var C={get tld(){return s()},set tld(Q){s(Q),mn()},get transfer(){return f()},set transfer(Q){f(Q),mn()},get locale(){return c()},set locale(Q){c(Q),mn()},get resolvedEntities(){return d()},set resolvedEntities(Q){d(Q),mn()},get isMobile(){return m()},set isMobile(Q=!1){m(Q),mn()},get translations(){return p()},set translations(Q){p(Q),mn()},get historical(){return w()},set historical(Q=!0){w(Q),mn()}},$=Ac(),R=vn($),M=vn(R);{var W=Q=>{var an=jc(),xn=vn(an,!0);hn(an),qn(()=>{ge(an,"href",V(I)),Bn(xn,V(D))}),on(Q,an)},en=Q=>{var an=Pt();qn(()=>Bn(an,V(D))),on(Q,an)};Fn(M,Q=>{m()?Q(W):Q(en,!1)})}hn(R);var q=wn(R,2),ln=vn(q);{let Q=ne(()=>{var an,xn;return(xn=(an=f())==null?void 0:an.details)==null?void 0:xn.date});fl(ln,{get date(){return V(Q)},get locale(){return c()}})}hn(q);var H=wn(q,2),Z=vn(H);{var Pn=Q=>{{let an=ne(()=>{var xn,Sn;return(Sn=(xn=f())==null?void 0:xn.details)==null?void 0:Sn.seasonId});Qa(Q,{get seasonId(){return V(an)},get club(){return V(T)},get country(){return d().countries[f().transferSource.countryId]}})}},Wn=Q=>{var an=Oe(),xn=ee(an);{var Sn=Vn=>{var ve=Tc(),Ye=vn(ve);hn(ve),qn(()=>{ge(ve,"href",V(I)),ge(Ye,"src",V(j).crestUrl),ge(Ye,"alt",V(j).shortName||V(j).name)}),Cs("error",Ye,function(...tn){Ur==null||Ur.apply(this,tn)}),As(Ye),on(Vn,ve)};Fn(xn,Vn=>{V(j)&&Vn(Sn)},!0)}on(Q,an)};Fn(Z,Q=>{!m()&&V(T)?Q(Pn):Q(Wn,!1)})}hn(H);var Dn=wn(H,2),Cn=vn(Dn);{var zn=Q=>{{let an=ne(()=>{var xn,Sn;return(Sn=(xn=f())==null?void 0:xn.details)==null?void 0:Sn.seasonId});Qa(Q,{get seasonId(){return V(an)},get club(){return V(j)},get country(){var xn,Sn;return d().countries[(Sn=(xn=f())==null?void 0:xn.transferDestination)==null?void 0:Sn.countryId]}})}};Fn(Cn,Q=>{V(j)&&Q(zn)})}hn(Dn);var Un=wn(Dn,2),bn=vn(Un);{var sn=Q=>{var an=Ic();on(Q,an)},te=Q=>{var an=Pt();qn(xn=>Bn(an,xn),[()=>{var xn,Sn,Vn;return i.format(((Vn=(Sn=(xn=f())==null?void 0:xn.details)==null?void 0:Sn.marketValue)==null?void 0:Vn.value)??0,s()??"")}]),on(Q,an)};Fn(bn,Q=>{var an,xn,Sn;((Sn=(xn=(an=f())==null?void 0:an.details)==null?void 0:xn.marketValue)==null?void 0:Sn.value)===0?Q(sn):Q(te,!1)})}hn(Un);var Rn=wn(Un,2),En=vn(Rn);{var nn=Q=>{var an=Oc(),xn=vn(an);{let Sn=ne(()=>{var ve,Ye;return(Ye=(ve=f())==null?void 0:ve.details)==null?void 0:Ye.fee}),Vn=ne(()=>{var ve;return(ve=f())==null?void 0:ve.typeDetails});Za(xn,{get translations(){return p()},get fee(){return V(Sn)},get typeDetails(){return V(Vn)},get tld(){return s()}})}hn(an),qn(()=>ge(an,"href",V(I))),on(Q,an)},Nn=Q=>{{let an=ne(()=>{var Sn,Vn;return(Vn=(Sn=f())==null?void 0:Sn.details)==null?void 0:Vn.fee}),xn=ne(()=>{var Sn;return(Sn=f())==null?void 0:Sn.typeDetails});Za(Q,{get translations(){return p()},get fee(){return V(an)},get typeDetails(){return V(xn)},get tld(){return s()}})}};Fn(En,Q=>{m()?Q(nn):Q(Nn,!1)})}hn(Rn);var Xn=wn(Rn,2);return hn($),qn(()=>{var Q,an,xn,Sn,Vn,ve,Ye,tn,ai,ar,On,Tt,Ki,lt,qi,Vi,Hi,Gi,Ar,Fe,oi,It,si,Ot,ft,ut,Cr,At,zr,or;ge(Xn,"href",V(I)),ge(Xn,"aria-label",`${((xn=d().players[(an=(Q=f())==null?void 0:Q.details)==null?void 0:an.playerId])==null?void 0:xn.name)??""} - 
      ${(((ve=d().clubs[(Vn=(Sn=f())==null?void 0:Sn.transferSource)==null?void 0:Vn.clubId])==null?void 0:ve.shortName)||((ai=d().clubs[(tn=(Ye=f())==null?void 0:Ye.transferSource)==null?void 0:tn.clubId])==null?void 0:ai.name))??""} -> ${(((Tt=d().clubs[(On=(ar=f())==null?void 0:ar.transferDestination)==null?void 0:On.clubId])==null?void 0:Tt.shortName)||((qi=d().clubs[(lt=(Ki=f())==null?void 0:Ki.transferDestination)==null?void 0:lt.clubId])==null?void 0:qi.name))??""}`),ge(Xn,"title",`${((Gi=d().players[(Hi=(Vi=f())==null?void 0:Vi.details)==null?void 0:Hi.playerId])==null?void 0:Gi.name)??""} - ${(((oi=d().clubs[(Fe=(Ar=f())==null?void 0:Ar.transferSource)==null?void 0:Fe.clubId])==null?void 0:oi.shortName)||((Ot=d().clubs[(si=(It=f())==null?void 0:It.transferSource)==null?void 0:si.clubId])==null?void 0:Ot.name))??""} -> ${(((Cr=d().clubs[(ut=(ft=f())==null?void 0:ft.transferDestination)==null?void 0:ut.clubId])==null?void 0:Cr.shortName)||((or=d().clubs[(zr=(At=f())==null?void 0:At.transferDestination)==null?void 0:zr.clubId])==null?void 0:or.name))??""}`)}),on(t,$),le(C)}he(ul,{tld:{},transfer:{},locale:{},resolvedEntities:{},isMobile:{},translations:{},historical:{}},[],[],!0);const zc={hash:"svelte-136v77",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}`};function no(t,r){se(r,!0),$e(t,zc);let i=pn(r,"tld",7),s=pn(r,"transfers",23,()=>[]),f=pn(r,"locale",7),c=pn(r,"resolvedEntities",7),d=pn(r,"appendFirstTransfer",7,!1),m=pn(r,"isMobile",7,!1),p=pn(r,"translations",7),w=pn(r,"historical",7,!0);const I=$=>{if($.length===0)return $;const R=$[$.length-1];return[...$,{id:R.id,transferDestination:R.transferSource,transferSource:R.transferSource,details:{age:R.details.age,contractUntilDate:R.details.contractUntilDate,isPending:R.details.isPending,marketValue:R.details.marketValue,playerId:R.details.playerId,remainingContractPeriod:R.details.remainingContractPeriod,seasonId:R.details.seasonId},typeDetails:{},relativeUrl:R.relativeUrl}]};let T=ne(()=>d()?I(s()):s());var j={get tld(){return i()},set tld($){i($),mn()},get transfers(){return s()},set transfers($=[]){s($),mn()},get locale(){return f()},set locale($){f($),mn()},get resolvedEntities(){return c()},set resolvedEntities($){c($),mn()},get appendFirstTransfer(){return d()},set appendFirstTransfer($=!1){d($),mn()},get isMobile(){return m()},set isMobile($=!1){m($),mn()},get translations(){return p()},set translations($){p($),mn()},get historical(){return w()},set historical($=!0){w($),mn()}},D=Oe(),C=ee(D);return Va(C,17,()=>V(T),qa,($,R)=>{var M=Oe(),W=ee(M);{var en=q=>{ul(q,{get historical(){return w()},get isMobile(){return m()},get locale(){return f()},get resolvedEntities(){return c()},get tld(){return i()},get transfer(){return V(R)},get translations(){return p()}})};Fn(W,q=>{V(R).details.seasonId&&q(en)})}on($,M)}),on(t,D),le(j)}he(no,{tld:{},transfers:{},locale:{},resolvedEntities:{},appendFirstTransfer:{},isMobile:{},translations:{},historical:{}},[],[],!0);var Dc=Mn('<details class="svelte-1svreww"><summary class="info-content svelte-1svreww"> </summary></details>');const Rc={hash:"svelte-1svreww",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}details.svelte-1svreww {box-sizing:border-box;color:#333333;cursor:pointer;display:block;font-family:'SourceSansPro-VF', sans-serif;font-size:0.75rem;font-style:normal;font-variation-settings:"wght" 400;font-weight:initial;line-height:0.75rem;}details.svelte-1svreww > summary:where(.svelte-1svreww) {box-sizing:border-box;display:block;max-height:30px;overflow:hidden;text-overflow:ellipsis;transition:all 0.3s ease;white-space:nowrap;}details.svelte-1svreww > summary:where(.svelte-1svreww)::-webkit-details-marker, details.svelte-1svreww > summary:where(.svelte-1svreww)::marker {box-sizing:border-box;display:none;}details.svelte-1svreww > summary.info-content:where(.svelte-1svreww) {background-color:#e4e4e4;font-family:"Oswald", "Oswald-fallback", sans-serif;font-size:0.75rem;font-style:normal;height:1.875rem;line-height:1.125rem;margin:0 0 0.3125rem;padding:0.375rem 1.5625rem 0.375rem 0.3125rem;position:relative;}details.svelte-1svreww > summary.info-content:where(.svelte-1svreww)::before {background-color:#1a3151;border-radius:0.5rem;box-sizing:border-box;color:white;content:"i";display:inline-block;font-family:"Times New Roman", sans-serif;font-size:1rem;font-style:italic;font-weight:700;height:1rem;line-height:1rem;margin-right:0.3125rem;padding-right:0.0625rem;text-align:center;text-transform:lowercase;width:1rem;}details.svelte-1svreww > summary.info-content:where(.svelte-1svreww)::after {background-image:url("https://tmsi.akamaized.net/pfeile/drop-down-arrow-blue.svg");background-position-y:center;background-repeat:no-repeat;background-size:100%;bottom:auto;box-sizing:border-box;content:"";cursor:pointer;display:block;height:1.875rem;position:absolute;right:0.3125rem;top:0;transform:rotate(0deg);transition:transform 0.3s ease;width:0.625rem;}details[open].svelte-1svreww > summary:where(.svelte-1svreww) {height:fit-content;max-height:200px;white-space:normal;}details[open].svelte-1svreww > summary.info-content:where(.svelte-1svreww)::after {transform:rotate(180deg);}`};function cl(t,r){se(r,!0),$e(t,Rc);let i=pn(r,"text",7);var s={get text(){return i()},set text(m){i(m),mn()}},f=Oe(),c=ee(f);{var d=m=>{var p=Dc(),w=vn(p),I=vn(w,!0);hn(w),hn(p),qn(()=>Bn(I,i())),on(m,p)};Fn(c,m=>{i()&&m(d)})}return on(t,f),le(s)}he(cl,{text:{}},[],[],!0);var $c=Mn('<section class="svelte-zrd04z"> </section>');const Mc={hash:"svelte-zrd04z",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}section.svelte-zrd04z {align-items:center;background-color:#f2f2f2;border-bottom:0.0625rem solid #e4e4e4;color:#57585a;font-family:'SourceSansPro-VF', sans-serif;font-size:0.75rem;font-style:normal;font-variation-settings:"wght" 700;font-weight:initial;padding:0.3125rem;}`};function eo(t,r){se(r,!0),$e(t,Mc);let i=pn(r,"text",7);var s={get text(){return i()},set text(d){i(d),mn()}},f=$c(),c=vn(f,!0);return hn(f),qn(()=>Bn(c,i())),on(t,f),le(s)}he(eo,{text:{}},[],[],!0);var Fc=Mn('<section class="svelte-1wruznw"><div class="hint svelte-1wruznw"> </div> <div class="fee svelte-1wruznw"> </div></section>');const Bc={hash:"svelte-1wruznw",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}section.svelte-1wruznw {align-items:normal;background-color:#f2f2f2;border-bottom:0.0625rem solid #e4e4e4;color:#57585a;display:grid;font-family:'SourceSansPro-VF', sans-serif;font-size:0.75rem;font-style:normal;font-variation-settings:"wght" 700;font-weight:initial;grid-template:"hint fee" 1.75rem/auto 4.6875rem;padding:0;}
@media print, (min-width: 769px) {section.svelte-1wruznw {grid-template:"hint fee fee" 1.75rem/auto 4.6875rem 1.0625rem;}
}.hint.svelte-1wruznw {align-items:center;display:flex;grid-area:hint;justify-content:flex-end;line-height:1;padding:0 0.25rem;}.fee.svelte-1wruznw {align-items:flex-end;display:flex;flex-direction:column;font-weight:unset;grid-area:fee;justify-content:center;line-height:1;padding:0 0.25rem;text-align:right;}`};function dl(t,r){se(r,!0),$e(t,Bc);let i=pn(r,"label",7),s=pn(r,"value",7);var f={get label(){return i()},set label(p){i(p),mn()},get value(){return s()},set value(p){s(p),mn()}},c=Oe(),d=ee(c);{var m=p=>{var w=Fc(),I=vn(w),T=vn(I);hn(I);var j=wn(I,2),D=vn(j,!0);hn(j),hn(w),qn(()=>{Bn(T,`${i()??""}:`),Bn(D,s())}),on(p,w)};Fn(d,p=>{i()&&s()&&p(m)})}return on(t,c),le(f)}he(dl,{label:{},value:{}},[],[],!0);var Nc=Mn("<!> <!> <!>",1),Lc=Mn("<!> <!> <!> <!> <!> <!>",1),Kc=Mn('<div class="box"><!> <!></div>');const qc={hash:"svelte-yljlyx",code:`/* stylelint-disable */
/* stylelint-enable */
/* UniviaPro font weights temporarily disabled
$tm-univia-fw: (
  'ultra-light': 100,
  'thin': 200,
  'light': 300,
  'book': 400,
  'regular': 450,
  'medium': 500,
  'bold': 700,
  'black': 900,
  'ultra': 950
);
*/
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
/* stylelint-disable */
/* stylelint-enable */
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}
@container (max-width: 340px) {
}
@media print, (min-width: 769px) {
}

@font-face {font-display:swap;font-family:"SourceSansPro-VF";font-style:normal;font-weight:normal;src:url("https://tmsi.akamaized.net/fonts/SourceSans3VF-Roman.woff2") format("woff2");
}
@font-face {ascent-override:160%;font-family:"Oswald-fallback";size-adjust:81.94%;src:local("Arial");
}
@font-face {ascent-override:111%;font-family:"Source Sans Pro-fallback";size-adjust:93.75%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSL-fallback";size-adjust:73.1%;src:local("Arial");
}
@font-face {ascent-override:110%;font-family:"OSB-fallback";size-adjust:62.7%;src:local("Arial");
}
/* UniviaPro font temporarily disabled
@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLight.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 100;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraLightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Thin.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 200;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-ThinItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Light.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 300;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-LightItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Book.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 400;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BookItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Regular.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 450;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Italic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Medium.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 500;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-MediumItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Bold.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 700;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BoldItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Black.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 900;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-BlackItalic.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: normal;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-Ultra.woff2') format('woff2');
}

@font-face {
  font-display: swap;
  font-family: 'UniviaPro';
  font-style: italic;
  font-weight: 950;
  src: url('https://tmsi.akamaized.net/fonts/UniviaPro-UltraItalic.woff2') format('woff2');
}
*/
@media print, (max-width: 768px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media print, (min-width: 769px) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}
@media (hover: hover) {
}:host {background-color:white;border:0.0625rem solid #dddddd;display:block;margin-top:0.625rem;}`};function hl(t,r){se(r,!0),$e(t,qc);const i=new Ja;let s=pn(r,"playerId",7),f=pn(r,"translations",7),c=pn(r,"eager",7,!1),d=ye(!0),m=ye(void 0),p=ye(!1),w=ye("com"),I=ye("en-US"),T=ye(void 0),j=ye(Xt({}));const D=async()=>{var q;if(we(T,await Du(s()),!0),!V(T)){$u(V(m));return}we(j,await tc({clubs:V(T).clubIds,players:[V(T).playerId]}),!0),we(w,((q=document.body)==null?void 0:q.getAttribute("data-tm-tld"))??"com",!0),we(I,Ws[V(w)]??"en-US",!0),we(d,!1)};$s(async()=>{if(c()){await D();return}Ru(V(m),D)});var C={get playerId(){return s()},set playerId(q){s(q),mn()},get translations(){return f()},set translations(q){f(q),mn()},get eager(){return c()},set eager(q=!1){c(q),mn()}},$=Kc(),R=vn($);rl(R,{get isMobile(){return V(p)},set isMobile(q){we(p,q,!0)}});var M=wn(R,2);{var W=q=>{ol(q,{get isMobile(){return V(p)}})},en=q=>{var ln=Lc(),H=ee(ln);{let Un=ne(()=>{var bn;return((bn=f())==null?void 0:bn.headline)??"Player Transfer History"});tl(H,{elementType:"h2",get headline(){return V(Un)}})}var Z=wn(H,2);{let Un=ne(()=>{var bn;return(bn=f())==null?void 0:bn.infotext});cl(Z,{get text(){return V(Un)}})}var Pn=wn(Z,2);sl(Pn,{get translations(){return f()}});var Wn=wn(Pn,2);{var Dn=Un=>{var bn=Nc(),sn=ee(bn);{let En=ne(()=>{var nn;return((nn=f())==null?void 0:nn.upcomingTransfers)??""});eo(sn,{get text(){return V(En)}})}var te=wn(sn,2);no(te,{historical:!1,get transfers(){return V(T).history.pending},get locale(){return V(I)},get tld(){return V(w)},get isMobile(){return V(p)},get resolvedEntities(){return V(j)},get translations(){return f()}});var Rn=wn(te,2);{let En=ne(()=>{var nn;return((nn=f())==null?void 0:nn.transferHistory)??""});eo(Rn,{get text(){return V(En)}})}on(Un,bn)};Fn(Wn,Un=>{var bn,sn,te,Rn,En;(sn=(bn=V(T))==null?void 0:bn.history)!=null&&sn.pending&&((En=(Rn=(te=V(T))==null?void 0:te.history)==null?void 0:Rn.pending)!=null&&En.length)&&Un(Dn)})}var Cn=wn(Wn,2);{let Un=ne(()=>{var bn,sn;return((sn=(bn=V(T))==null?void 0:bn.history)==null?void 0:sn.terminated)??[]});no(Cn,{get transfers(){return V(Un)},get locale(){return V(I)},get tld(){return V(w)},get isMobile(){return V(p)},get appendFirstTransfer(){return V(p)},get resolvedEntities(){return V(j)},get translations(){return f()}})}var zn=wn(Cn,2);{let Un=ne(()=>{var sn;return((sn=f())==null?void 0:sn.feesum)??""}),bn=ne(()=>{var sn;return i.format(((sn=V(T))==null?void 0:sn.history.feeSum.value)??0,V(w)??"")});dl(zn,{get label(){return V(Un)},get value(){return V(bn)}})}on(q,ln)};Fn(M,q=>{V(d)?q(W):q(en,!1)})}return hn($),_u($,q=>we(m,q),()=>V(m)),on(t,$),le(C)}return customElements.define("tm-player-transfer-history",he(hl,{playerId:{attribute:"player-id",type:"String"},translations:{attribute:"translations",type:"Object"},eager:{attribute:"eager",type:"Boolean"}},[],[],!0)),hl})();

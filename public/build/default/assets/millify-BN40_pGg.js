import{aa as N}from"./app-BDqFx3nJ.js";var l={},f={},_;function F(){return _||(_=1,Object.defineProperty(f,"__esModule",{value:!0}),f.defaultOptions=void 0,f.defaultOptions={lowercase:!1,precision:1,space:!1,units:["","K","M","B","T","P","E"]}),f}var r={},E;function R(){if(E)return r;E=1,Object.defineProperty(r,"__esModule",{value:!0}),r.getLocales=r.getFractionDigits=r.roundTo=r.parseValue=void 0;function c(t){const e=parseFloat(t==null?void 0:t.toString());if(isNaN(e))throw new Error("Input value is not a number");if(e>Number.MAX_SAFE_INTEGER||e<Number.MIN_SAFE_INTEGER)throw new RangeError("Input value is outside of safe integer range");return e}r.parseValue=c;function s(t,e){if(!Number.isFinite(t))throw new Error("Input value is not a finite number");if(!Number.isInteger(e)||e<0)throw new Error("Precision is not a positive integer");return Number.isInteger(t)?t:parseFloat(t.toFixed(e))}r.roundTo=s;function d(t){var e;if(Number.isInteger(t))return 0;const i=t.toString().split(".")[1];return(e=i==null?void 0:i.length)!==null&&e!==void 0?e:0}r.getFractionDigits=d;function g(){var t;return typeof navigator>"u"?[]:Array.from((t=navigator.languages)!==null&&t!==void 0?t:[])}return r.getLocales=g,r}var w;function x(){if(w)return l;w=1,Object.defineProperty(l,"__esModule",{value:!0}),l.millify=void 0;const c=F(),s=R(),d=1e3;function*g(e){let i=d;for(;;){const u=e/i;if(u<1)return;yield u,i*=d}}function t(e,i){var u,v;const n=i?{...c.defaultOptions,...i}:c.defaultOptions;if(!Array.isArray(n.units)||!n.units.length)throw new Error("Option `units` must be a non-empty array");let o;try{o=s.parseValue(e)}catch(a){return a instanceof Error&&console.warn(`WARN: ${a.message} (millify)`),String(e)}const I=o<0?"-":"";o=Math.abs(o);let p=0;for(const a of g(o))o=a,p+=1;if(p>=n.units.length)return e.toString();let m=s.roundTo(o,n.precision);for(const a of g(m))m=a,p+=1;const y=(u=n.units[p])!==null&&u!==void 0?u:"",O=n.lowercase?y.toLowerCase():y,b=n.space?" ":"",h=m.toLocaleString((v=n.locales)!==null&&v!==void 0?v:s.getLocales(),{minimumFractionDigits:s.getFractionDigits(m)});return`${I}${h}${b}${O}`}return l.millify=t,l.default=t,l}var M=x();const T=N(M);export{T as m};
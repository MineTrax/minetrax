import{U as u}from"./app-BDqFx3nJ.js";function l(){const t=u();function e(r){return t.props.permissions.includes(r)}function o(r){return t.props.permissions.some(n=>n.includes(r))}function i(r){return r?r.roles.some(n=>n.is_staff):!1}function s(r,n=null){return n||(n=t.props.auth.user),n?n==null?void 0:n.roles.some(f=>f.name===r):!1}return{can:e,canWild:o,isStaff:i,hasRole:s}}export{l as u};
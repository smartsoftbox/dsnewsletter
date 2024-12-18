/**
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

import{A as e,B as t,a,r as o,u as n,m as l,J as s,b as r,L as u,R as c,E as i,P as m,S as d,c as p,d as g,e as y,f as E}from"./vendor.js";const b=["Arial","Tahoma","Verdana","Times New Roman","Courier New","Georgia","Lato","Montserrat","黑体","仿宋","楷体","标楷体","华文仿宋","华文楷体","宋体","微软雅黑"].map((e=>({value:e,label:e}))),f=[{label:"Content",active:!0,blocks:[{type:e.TEXT},{type:e.IMAGE,payload:{attributes:{padding:"0px 0px 0px 0px"}}},{type:e.BUTTON},{type:e.SOCIAL},{type:e.DIVIDER},{type:e.SPACER},{type:e.HERO},{type:e.WRAPPER}]},{label:"Layout",active:!0,displayType:"column",blocks:[{title:"2 columns",payload:[["50%","50%"],["33%","67%"],["67%","33%"],["25%","75%"],["75%","25%"]]},{title:"3 columns",payload:[["33.33%","33.33%","33.33%"],["25%","25%","50%"],["50%","25%","25%"]]},{title:"4 columns",payload:[[["25%","25%","25%","25%"]]]}]}],x=t.getBlockByType(a.PAGE);function h(){const e=document.getElementsByName("design")[0].value;let t;t=e?JSON.parse(e):x.create({data:{value:{"content-background-color":"#ffffff"}}});const[a,E]=o.exports.useState(e?t.content:t),h=o.exports.useMemo((()=>({subject:e?t.subject:"Example subject",subTitle:e?t.subTitle:"Example sub title!",content:a})),[a]),{width:v}=n(),w=v<1400,S=o.exports.useCallback((async(e,t)=>{const a=l(s({data:e.content,mode:"production",context:e.content}),{beautify:!0,validationLevel:"soft"}).html;document.getElementsByName("html")[0].value=a,document.getElementsByName("design")[0].value=JSON.stringify(e),r.success("Saved success!")}),[]);if(!h)return null;const[T,L]=o.exports.useState(function(){let e=[],t=[];for(let a=1;a<=11;a++)e.push({name:"Example name",desc:"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",url:"product url",image:"product image"}),t.push({name:"Example name",desc:"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",url:"product url",image:"product image"});return{user:{first_name:"Ryan",last_name:"Jones"},shop:{name:"My Shop",logo:"logo",url:"url"},date:{normal:"",full:""},newsletter:{subscribe:"",unsubscribe:""},product_new:e,product_featured:t,product_id:[{name:"Example name",desc:"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",url:"product url",image:"product image"}]}}()),B=o.exports.useCallback((async(e,t)=>{const a=new u,o=a.parse(e);return await a.renderSync(o,t)}),[]);return c.createElement("div",null,c.createElement(i,{dashed:!1,data:h,height:"calc(100vh - 85px)",onUploadImage:async e=>{let t=new FormData;t.append("file",e);let a=window.location.href+"&ajax=1&action=uploadImage";const o=await fetch(a,{method:"POST",body:t});return(await o.json()).url},autoComplete:!0,enabledLogic:!0,fontList:b,onSubmit:S,mergeTags:T,mergeTagGenerate:e=>`{{${e}}}`,onBeforePreview:B},(({values:e},{submit:t})=>c.createElement(c.Fragment,null,c.createElement(m,{title:"Edit",extra:c.createElement(d,{alignment:"center"},c.createElement(p,{id:"addEasyEmail",type:"primary",onClick:()=>t()},"Save"))}),c.createElement(g,{compact:!w,categories:f,showSourceCode:!0},c.createElement(y,null))))))}E.render(c.createElement(h,null),document.getElementById("root"));

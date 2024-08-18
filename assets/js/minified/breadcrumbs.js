"use strict";(function(wp,lodash){"use strict";if(!wp){return}var map=lodash.map,reverse=lodash.reverse;var _wp$element=wp.element,el=_wp$element.createElement,Fragment=_wp$element.Fragment,useMemo=_wp$element.useMemo;var registerBlockType=wp.blocks.registerBlockType;var _wp$blockEditor=wp.blockEditor,useBlockProps=_wp$blockEditor.useBlockProps,InspectorControls=_wp$blockEditor.InspectorControls;var useSelect=wp.data.useSelect;var _wp$components=wp.components,PanelBody=_wp$components.PanelBody,TextControl=_wp$components.TextControl,ToggleControl=_wp$components.ToggleControl;var decodeEntities=wp.htmlEntities.decodeEntities;var _wp$primitives=wp.primitives,SVG=_wp$primitives.SVG,Path=_wp$primitives.Path;var _wp$i18n=wp.i18n,__=_wp$i18n.__,sprintf=_wp$i18n.sprintf;registerBlockType("seo-ready/breadcrumbs",{apiVersion:2,title:__("Breadcrumbs","seo-ready"),description:__("This block displays trail of breadcrumb tracks to help visitors show exactly where they are on the website.","seo-ready"),keywords:["menu","navigation","path","trail"],icon:el(SVG,{width:24,height:24,viewBox:"0 0 19 6",fill:"none"},el("rect",{x:"0.5",width:"3",height:"2"}),el("rect",{x:"15.5",y:"4",width:"3",height:"2"}),el(Path,{d:"M4 0H5C5.55228 0 6 0.447715 6 1V6H5C4.44772 6 4 5.55228 4 5V0Z"}),el(Path,{d:"M8.5 0H9.5C10.0523 0 10.5 0.447715 10.5 1V6H9.5C8.94772 6 8.5 5.55228 8.5 5V0Z"}),el(Path,{d:"M13 0H14C14.5523 0 15 0.447715 15 1V6H14C13.4477 6 13 5.55228 13 5V0Z"}),el(Path,{d:"M8 3.82843L6.5 5.32843L6.5 2.5L8 1L8 3.82843Z"}),el(Path,{d:"M12.5 3.82843L11 5.32843L11 2.5L12.5 1L12.5 3.82843Z"})),attributes:{delimiter:{type:"string","default":"\u2192"},hideCurrentPageTrail:{type:"boolean","default":false},hideLeadingDelimiter:{type:"boolean","default":true},hideSiteTitle:{type:"boolean","default":false},siteTitleOverride:{type:"string","default":""}},category:"design",usesContext:["postId","postType"],supports:{align:["full","wide"],multiple:false,color:{gradients:false,link:true,__experimentalDefaultControls:{background:true,text:true}},layout:{allowSwitching:false,allowInheriting:false,allowEditing:false,"default":{type:"flex",flexWrap:"nowrap"}},spacing:{margin:["top","bottom"],padding:true},typography:{fontSize:true,lineHeight:true,__experimentalFontFamily:true,__experimentalFontWeight:true,__experimentalFontStyle:true,__experimentalTextTransform:true,__experimentalTextDecoration:true,__experimentalLetterSpacing:true,__experimentalDefaultControls:{fontSize:true}}},edit:function edit(_ref){var attributes=_ref.attributes,_ref$context=_ref.context,postId=_ref$context.postId,postType=_ref$context.postType,setAttributes=_ref.setAttributes;var delimiter=attributes.delimiter,hideCurrentPageTrail=attributes.hideCurrentPageTrail,hideLeadingDelimiter=attributes.hideLeadingDelimiter,hideSiteTitle=attributes.hideSiteTitle,siteTitleOverride=attributes.siteTitleOverride;var blockProps=useBlockProps({className:"is-layout-flex",style:{boxSizing:"border-box",listStyleType:"none",paddingLeft:0,margin:0}});var _useSelect=useSelect(function(select){var _currentPost$categori;var _select=select("core"),getEntityRecord=_select.getEntityRecord,getEditedEntityRecord=_select.getEditedEntityRecord;var siteData=getEntityRecord("root","__unstableBase");var _currentPost=getEditedEntityRecord("postType",postType,postId);if(!_currentPost){return{siteTitle:decodeEntities(siteData===null||siteData===void 0?void 0:siteData.name),categories:[],currentPost:null,parents:[]}}var getParentEntities=function getParentEntities(initialId,entityType){var parentEntities=[];var currentParentId=initialId;while(currentParentId){var parent=getEntityRecord(entityType,entityType==="postType"?postType:"category",currentParentId);if(!parent)break;parentEntities.push(parent);currentParentId=parent.parent||null}return parentEntities};var parentCategories=getParentEntities(_currentPost===null||_currentPost===void 0?void 0:(_currentPost$categori=_currentPost.categories)===null||_currentPost$categori===void 0?void 0:_currentPost$categori[0],"taxonomy");var parentEntities=getParentEntities(_currentPost===null||_currentPost===void 0?void 0:_currentPost.parent,"postType");return{siteTitle:decodeEntities(siteData===null||siteData===void 0?void 0:siteData.name),categories:parentCategories,currentPost:_currentPost,parents:reverse(parentEntities)}},[postId,postType]),siteTitle=_useSelect.siteTitle,categories=_useSelect.categories,currentPost=_useSelect.currentPost,parents=_useSelect.parents;var trails=useMemo(function(){var _trails=[];if(parents!==null&&parents!==void 0&&parents.length){_trails=map(parents,function(parent){var _parent$title;return(parent===null||parent===void 0?void 0:(_parent$title=parent.title)===null||_parent$title===void 0?void 0:_parent$title.rendered)||" "})}else if(categories!==null&&categories!==void 0&&categories.length){_trails=map(categories,function(category){return(category===null||category===void 0?void 0:category.name)||" "})}else{_trails=[__("Top-level page","seo-ready"),__("Child page","seo-ready")]}if(!hideSiteTitle&&siteTitle){_trails.unshift(siteTitleOverride||siteTitle)}if(!hideCurrentPageTrail){_trails.push((currentPost===null||currentPost===void 0?void 0:currentPost.title)||__("Current page","seo-ready"))}return _trails},[categories,parents,hideCurrentPageTrail,hideSiteTitle,siteTitle,siteTitleOverride]);return el(Fragment,{},el("ol",blockProps,map(trails,function(crumbTitle,index){return el("li",{key:index,className:"wp-block-seo-ready-breadcrumbs__crumb"},delimiter&&index===0&&!hideSiteTitle&&!hideLeadingDelimiter&&el("span",{className:"wp-block-seo-ready-breadcrumbs__delimiter",style:{marginRight:"var(--wp--style--block-gap, 0.5em)"}},delimiter),el("a",{href:"#",onClick:function onClick(event){return event.preventDefault()}},crumbTitle),delimiter&&index<trails.length-1&&el("span",{className:"wp-block-seo-ready-breadcrumbs__delimiter",style:{marginLeft:"var(--wp--style--block-gap, 0.5em)"}},delimiter))})),el(InspectorControls,{},el(PanelBody,{initialOpen:true,title:__("Display Settings","seo-ready")},el(TextControl,{autoComplete:"off",autoCapitalize:"none",label:__("Delimiter","seo-ready"),onChange:function onChange(value){return setAttributes({delimiter:value})},placeholder:sprintf(__("e.g. %s","seo-ready"),"\u2192"),value:delimiter||""}),delimiter&&el(ToggleControl,{checked:Boolean(hideLeadingDelimiter),label:sprintf(__("%s leading delimiter?","seo-ready"),hideLeadingDelimiter?"Hide":"Show"),onChange:function onChange(){return setAttributes({hideLeadingDelimiter:!hideLeadingDelimiter})}}),el(ToggleControl,{checked:Boolean(hideCurrentPageTrail),label:sprintf(__("%s current page title?","seo-ready"),hideCurrentPageTrail?"Hide":"Show"),onChange:function onChange(){return setAttributes({hideCurrentPageTrail:!hideCurrentPageTrail})}}),el(ToggleControl,{checked:Boolean(hideSiteTitle),label:sprintf(__("%s site title?","seo-ready"),hideSiteTitle?"Hide":"Show"),onChange:function onChange(){return setAttributes({hideSiteTitle:!hideSiteTitle})}}),!hideSiteTitle&&el(TextControl,{autoComplete:"off",autoCapitalize:"none",label:__("Site Title Override","seo-ready"),onChange:function onChange(value){return setAttributes({siteTitleOverride:value})},placeholder:__("e.g. Home","seo-ready"),value:siteTitleOverride||""}))))},save:function save(){var blockProps=useBlockProps.save({id:"breadcrumb",className:"is-layout-flex",style:{boxSizing:"border-box",listStyleType:"none",paddingLeft:0,margin:0}});return el("ol",blockProps)}})})(window.wp,window.lodash);
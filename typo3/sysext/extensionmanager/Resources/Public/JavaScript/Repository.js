/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
define(["require","exports","jquery","nprogress","TYPO3/CMS/Backend/Modal","TYPO3/CMS/Backend/Notification","TYPO3/CMS/Backend/Severity","TYPO3/CMS/Core/Ajax/AjaxRequest","TYPO3/CMS/Core/Event/RegularEvent","tablesort","TYPO3/CMS/Backend/Input/Clearable"],(function(e,n,t,s,o,a,l,r,i){"use strict";return class{constructor(){this.downloadPath="",this.getDependencies=async e=>{const n=await e.resolve();s.done(),n.hasDependencies?o.confirm(n.title,t(n.message),l.info,[{text:TYPO3.lang["button.cancel"],active:!0,btnClass:"btn-default",trigger:()=>{o.dismiss()}},{text:TYPO3.lang["button.resolveDependencies"],btnClass:"btn-info",trigger:()=>{this.getResolveDependenciesAndInstallResult(n.url+"&tx_extensionmanager_tools_extensionmanagerextensionmanager[downloadPath]="+this.downloadPath),o.dismiss()}}]):n.hasErrors?a.error(n.title,n.message,15):this.getResolveDependenciesAndInstallResult(n.url+"&tx_extensionmanager_tools_extensionmanagerextensionmanager[downloadPath]="+this.downloadPath)}}initDom(){s.configure({parent:".module-loading-indicator",showSpinner:!1});const e=document.getElementById("terVersionTable"),n=document.getElementById("terSearchTable");null!==e&&new Tablesort(e),null!==n&&new Tablesort(n),this.bindDownload(),this.bindSearchFieldResetter()}bindDownload(){const e=this;new i("click",(function(n){n.preventDefault();const t=this.closest("form"),o=t.dataset.href;e.downloadPath=t.querySelector("input.downloadPath:checked").value,s.start(),new r(o).get().then(e.getDependencies)})).delegateTo(document,".downloadFromTer form.download button[type=submit]")}getResolveDependenciesAndInstallResult(e){s.start(),new r(e).get().then(async e=>{const n=await e.raw().json();if(n.errorCount>0)o.confirm(n.errorTitle,t(n.errorMessage),l.error,[{text:TYPO3.lang["button.cancel"],active:!0,btnClass:"btn-default",trigger:()=>{o.dismiss()}},{text:TYPO3.lang["button.resolveDependenciesIgnore"],btnClass:"btn-danger disabled t3js-dependencies",trigger:e=>{t(e.currentTarget).hasClass("disabled")||(this.getResolveDependenciesAndInstallResult(n.skipDependencyUri),o.dismiss())}}]),o.currentModal.on("shown.bs.modal",()=>{const e=o.currentModal.find(".t3js-dependencies");t('input[name="unlockDependencyIgnoreButton"]',o.currentModal).on("change",n=>{e.toggleClass("disabled",!t(n.currentTarget).prop("checked"))})});else{let e=TYPO3.lang["extensionList.dependenciesResolveDownloadSuccess.message"+n.installationTypeLanguageKey].replace(/\{0\}/g,n.extension);e+="\n"+TYPO3.lang["extensionList.dependenciesResolveDownloadSuccess.header"]+": ",t.each(n.result,(n,s)=>{e+="\n\n"+TYPO3.lang["extensionList.dependenciesResolveDownloadSuccess.item"]+" "+n+": ",t.each(s,n=>{e+="\n* "+n})}),a.info(TYPO3.lang["extensionList.dependenciesResolveFlashMessage.title"+n.installationTypeLanguageKey].replace(/\{0\}/g,n.extension),e,15),top.TYPO3.ModuleMenu.App.refreshMenu()}}).finally(()=>{s.done()})}bindSearchFieldResetter(){let e;if(null!==(e=document.querySelector('.typo3-extensionmanager-searchTerForm input[type="text"]'))){const n=""!==e.value;e.clearable({onClear:e=>{n&&e.closest("form").submit()}})}}}}));
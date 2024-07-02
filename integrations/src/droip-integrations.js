import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";

// console.log('kirki react root!', document.getElementById('kirki-react-root'));
const prefix = 'prefix';
const reactRoot = document.createElement("div");
reactRoot.classList.add('droip-integrations-wrapper');
const root = createRoot(reactRoot);
document.body.append(reactRoot);
root.render(<App />);

// // const { registerPlugin } = wp.plugins;
// const {
// 	PluginSidebar,
// 	__experimentalMainDashboardButton,
// 	PluginBlockSettingsMenuItem,
// } = wp.editPost;
// const { __ } = wp.i18n;

// const PluginSidebarTest = () => (
//     <PluginSidebar name="plugin-sidebar-test">
//         <p>Plugin Sidebar</p>
//     </PluginSidebar>
// );

// registerPlugin("juicy-sideabr", {
// 	icon: "smiley",
// 	render: () => {
// 		return (
// 			<>
// 				<PluginSidebarTest></PluginSidebarTest>
// 				{/* <PluginSidebar name="juicy-guten" title={__('Meta options', 'wefk')}>
//                     Sidebar content area
//                 </PluginSidebar> */}
// 				<__experimentalMainDashboardButton>
// 					Droip Templates
// 				</__experimentalMainDashboardButton>
// 				{/* <PluginBlockSettingsMenuItem>
//                     Custom main dashboard button content
//                 </PluginBlockSettingsMenuItem> */}
// 			</>
// 		);
// 	},
// });

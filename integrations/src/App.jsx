const { __ } = wp.i18n;
import { useState , useEffect } from 'react';
import "../sass/index.scss";
import Modal from './components/Modal';

const App = () => {
	const [stepper, setStepper] = useState({
		'install-plugin': false,
		'import-template': false
	});
	const [droipTemplates, setDroipTemplates] = useState([]);
	const [showModal, setShowModal] = useState(false);
	const [loading, setLoading] = useState(false); 
	const [isImport, setIsImport] = useState(false);
	const [error, setIsError] = useState(false);
	const [status, setStatus] = useState('');
	const [pageDetails, setPageDetails] = useState(null);
	// const slug = "https://downloads.wordpress.org/plugin/tutor.2.7.1.zip";
	const slug = "https://downloads.wordpress.org/plugin/tutor.2.7.1.zip";
	const restapi =
		"http://droip.test/wp-json/droiptemplate-provider/v1/driop-pages";

	const installDroip = async (id) => {
		setShowModal(true);
		// setTimeout(() => {
		// }, 1000);
		setLoading(id);
		setStatus('Installing droip...');
		setStepper( prevStep => ({ ...prevStep, 'install-plugin': 'on-going' }) );
		const pageId = document.getElementById('post_ID').value;
		const installPluginsData = new FormData();
		installPluginsData.append("action", "install_plugins");
		installPluginsData.append("droip_template_nonce", droipIntegrationObject.nonce_value);
		const installPluginReq = await fetch(droipIntegrationObject.ajax_url, {
			method: "POST",
			body: installPluginsData,
		});

		const installPluginResponse = await installPluginReq.json();
		// console.log(response);
		if (installPluginResponse.success) {
			// setLoading(false);
			// setIsImport(true);
			setStepper(prevStep => ({ ...prevStep, 'install-plugin': 'done' }));
			setStepper( prevStep => ({ ...prevStep, 'import-template': 'on-going' }) );
			setStatus(installPluginResponse.data.message);
			console.log('Success : ', installPluginResponse.data.message);
			setTimeout(async () => {
				setStatus('Now importing template...');
				const importTemplateData = new FormData();
				importTemplateData.append("action", "import_template");
				importTemplateData.append("droip_template_nonce", droipIntegrationObject.nonce_value);
				importTemplateData.append("page_id", pageId);
				importTemplateData.append("template_id", id);
				const importTemplateReq = await fetch(droipIntegrationObject.ajax_url, {
					method: "POST",
					body: importTemplateData,
				});
				const importTemplateResponse = await importTemplateReq.json();
				if (importTemplateResponse.success) {
					// setTimeout(() => {
						setStepper(prevStep => ({ ...prevStep, 'import-template': 'done' }));
						setLoading(false);
						setStatus(importTemplateResponse.data.message);
						setPageDetails(importTemplateResponse.data.pageDetails);
					// }, 1000);
				} else {
					setLoading(false);
					setIsError(response.data.message);
					setStatus('Template isn\'t imported!, something went wrong!!');
					console.log('Error ', importTemplateResponse.data.message);
				}
			}, 2000 );
		} else {
			setLoading(false);
			setIsError(response.data.message);
			setStatus('Droip isn\'t installed!, something went wrong!!');
			console.log('Error ', installPluginResponse.data.message);
		}
	};

	const modalCloser = () => {
		setShowModal(false);
	}

	async function fetchTemplates() {
		const _templatesResponse = await fetch(`${droipIntegrationObject.DRIOP_TEMPLATE_BASE_API}/droip-templates`);
		const _templates = await _templatesResponse.json();
		setDroipTemplates(_templates);
	}
	
	useEffect(() => {
		fetchTemplates();
	}, []);
	

	return (
		<div id="droip-integrations-root">
			<div>
				<h3>{__("Droip Template lists", "kirki")}</h3>
				<p> {status} </p>
				{/* { isImport && <p> {status} </p> } */}
				{ error && <p>{error}</p> }
			</div>
			{showModal && <Modal stepper={stepper} showModal={showModal} modalCloser={modalCloser} pageDetails={pageDetails} /> }
			<div className="droip-template-list">
				{console.log('droipTemplates=>', droipTemplates)}
				{droipTemplates
					.map((item, index) => (
						<div key={index} >
							<p>{item.post_title}</p>
							<img src={item.thumbnail_url ? item.thumbnail_url : ''} alt="" />
							<button onClick={() => installDroip(item.ID)} disabled={loading === item.ID}>
								{ loading === item.ID ? 'Loading...' : 'Import' }
							</button>
						</div>
					))}
			</div>
		</div>
	);
};

export default App;

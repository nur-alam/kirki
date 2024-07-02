import React from 'react'
import { useState } from 'react';

function Modal({ stepper, showModal, modalCloser, pageDetails }) {
    // const [modalCloser, setModalCloser] = useState(false);
    return (
        <div className='droip-kirki-modal-wrapper' style={{ display: `${showModal ? 'flex' : 'none'}` }} >
            <div className='droip-kirki-modal'>
                <div className='droip-kirki-modal-header'>
                    <div>Import Template</div>
                    <a onClick={() => modalCloser()} className='dashicons dashicons-no-alt'></a>
                </div>
                <div className='droip-kirki-modal-body'>
                    <ul className='droip-kirki-stepper'>
                        <li>
                            <span
                                className={`dashicon dashicons
                                    ${stepper['install-plugin'] == false ? 'dashicons-image-rotate' : ''}
                                    ${stepper['install-plugin'] == 'on-going' ? 'dashicons-image-rotate rotate-animation' : ''}
                                    ${stepper['install-plugin'] == 'done' ? 'dashicons-yes' : ''}
                                `}>
                                </span>
                            {/* <span className={`dashicon dashicons dashicons-image-rotate rotate-animation`}></span> */}
                            <span>Installing required plugins</span>
                        </li>
                        <li>
                            <span className={`dashicon dashicons 
                                ${stepper['import-template'] == false ? 'dashicons-image-rotate' : ''}
                                ${stepper['import-template'] == 'on-going' ? 'dashicons-image-rotate rotate-animation' : ''} 
                                ${stepper['import-template'] == 'done' ? 'dashicons-yes' : ''}
                            `}>
                            </span>
                            <span>Importing template</span>
                        </li>
                    </ul>
                </div>
                {pageDetails?.pageUrl &&
                    (
                        <div className='droip-kirki-modal-footer'>
                            <a className='tutor-btn tutor-btn-primary tutor-btn-sm ml-4' href={`${pageDetails.pageUrl}?action=droip&post_id=${pageDetails.pageId}`}>
                                Edit Page
                            </a>
                            <a className='tutor-btn tutor-btn-primary tutor-btn-sm' href={pageDetails.pageUrl} style={{marginLeft : '5px'}}>
                                View Page
                            </a>
                        </div>
                    )
                }
            </div>
        </div>
    )
}

export default Modal;
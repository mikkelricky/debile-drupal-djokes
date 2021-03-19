/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom'

const App = ({collection_data_url: collectionDataUrl}) => {
  return <pre>App {collectionDataUrl}</pre>
}

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')

ReactDOM.render(
    <App {...options} />,
  document.getElementById('app')
)

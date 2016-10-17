import React from 'react';
import ReactDOM from 'react-dom';
import Application from './components/Application/Application.jsx';
import webSocketClient from './libraries/WebSocket/WebSocketClient.js';

require("../node_modules/material-design-lite/material.min.css");
require("../node_modules/material-design-lite/dist/material.indigo-pink.min.css");
require("../node_modules/material-design-lite/material.min.js");
require("./style.css");

ReactDOM.render(
    <Application />,
    document.getElementById('application')
);

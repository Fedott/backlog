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

async function test() {
    // return await webSocket.sendRequest({type: "ping"});
    // return await webSocketClient.sendRequest({type: "get-stories"});
    return await webSocketClient.sendRequest({
        type: "create-story",
        "payload": {"number": "2", "title": "First story", "text": "Я как разработчик\nХочу что бы программа работала\nЧто бы получить много денег"}
    });
}

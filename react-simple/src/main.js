import React from 'react';
import ReactDOM from 'react-dom';
import Application from './components/Application/Application.jsx';
import webSocketClient from './libraries/WebSocket/WebSocketClient.js';

ReactDOM.render(
    <Application />,
    document.getElementById('application')
);

async function test() {
    // return await webSocket.sendRequest({type: "ping"});
    return await webSocketClient.sendRequest({type: "get-stories"});
    return await webSocketClient.sendRequest({
        type: "create-story",
        "payload": {"number": "1", "title": "First story", "text": "First story text"}
    });
}

import React from 'react';
import ReactDOM from 'react-dom';
import Application from './components/Application/Application.jsx';
import WebSocketClient from './libraries/WebSocket/WebSocketClient.js';

var IndexPage = React.createClass({
    componentWillMount: function () {
        console.log('mounted');
    },

    render: function () {
        return (
            <h2>Hello, {this.props.name}!</h2>
        )
    }
});

class ImproveIndexPage extends React.Component {
    render() {
        return (
            <h1>Hello again, {this.props.name}</h1>
        );
    }
}

class DoubleImproveIndexPage extends ImproveIndexPage {
    render() {
        return (
            <h1>{this.props.phrase}, {this.props.name}</h1>
        )
    }
}

// ReactDOM.render(
//     <Application />,
//     document.getElementById('example')
// );

var webSocket = new WebSocketClient("ws://localhost:8080/websocket");
webSocket.connect().then(function () {
    test().then(function (result) {
        console.log(result);
    });
});

async function test() {
    // return await webSocket.sendRequest({type: "ping"});
    return await webSocket.sendRequest({type: "get-stories"});
    return await webSocket.sendRequest({
        type: "create-story",
        "payload": {"number": "1", "title": "First story", "text": "First story text"}
    });
}

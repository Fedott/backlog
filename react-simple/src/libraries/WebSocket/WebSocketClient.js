import Request from "./Request";
import Response from "./Response";

export class WebSocketRequestExecutor {
    request: Request;
    resolveFunction;
    rejectFunction;

    constructor(request: Request) {
        this.request = request;
    }

    executor(resolve, reject) {
        this.resolveFunction = resolve;
        this.rejectFunction = reject;
    }
}

export class WebSocketClient {
    uri = "";
    webSocketConnection: WebSocket;
    status = "disconnected";

    connectPromisors = [];

    requestsIds = 0;
    requests = {};

    constructor(uri) {
        this.uri = uri;
    }

    async connect() {
        if (this.status == 'connected') {
            return Promise.resolve();
        }

        if (this.status == 'disconnected') {
            this.webSocketConnection = new WebSocket(this.uri);
            this.webSocketConnection.onmessage = this.onMessage.bind(this);
            this.webSocketConnection.onopen = this.connectResolve.bind(this);
            this.webSocketConnection.onerror = this.connectRejectOrClose.bind(this);
            this.webSocketConnection.onclose = this.connectRejectOrClose.bind(this);
            this.status = 'connecting';
        }

        return new Promise(function (resolve, reject) {
            this.connectPromisors.push({
                resolve: resolve,
                reject: reject,
            });
        }.bind(this));
    }

    connectResolve() {
        this.status = 'connected';

        this.connectPromisors.map((promisor) => {
            promisor['resolve']();
        })
    }

    connectRejectOrClose() {
        this.status = 'disconnected';

        this.connectPromisors.map((promisor) => {
            promisor['reject']();
        })
    }

    onMessage(event) {
        const response = JSON.parse(event.data);
        if (response.requestId) {
            const requestExecutor = this.requests[response.requestId];
            requestExecutor.resolveFunction(response);
        }
    }

    getNextRequestId() {
        return ++this.requestsIds;
    }

    async sendRequest(request: Request): Response {
        await this.connect();

        request.id = this.getNextRequestId();
        const requestExecutor = new WebSocketRequestExecutor(request);
        this.requests[request.id] = requestExecutor;

        this.webSocketConnection.send(JSON.stringify(request));

        return new Promise(function (resolve, reject) {
            requestExecutor.executor(resolve, reject);
        });
    }
}

let port = window.location.port == 3000 ? 8080 : window.location.port;
let protocol = window.location.protocol == 'https:' ? 'wss' : 'ws';
const webSocketClient = new WebSocketClient(protocol + "://" + window.location.hostname + ":" + port + "/websocket");

export default webSocketClient;


export class WebSocketRequestExecutor {
    request;
    resolveFunction;
    rejectFunction;

    constructor(request) {
        this.request = request;
    }

    executor(resolve, reject) {
        this.resolveFunction = resolve;
        this.rejectFunction = reject;
    }
}

class WebSocketClient {
    uri = "";
    webSocketConnection: WebSocket;

    requestsIds = 0;
    requests = {};

    constructor(uri) {
        this.uri = uri;
    }

    async connect() {
        this.webSocketConnection = new WebSocket(this.uri);
        this.webSocketConnection.onmessage = this.onMessage.bind(this);

        return new Promise(function (resolve, reject) {
            this.webSocketConnection.onopen = function () {
                resolve();
            };
            this.webSocketConnection.onerror = function () {
                reject();
            }
        }.bind(this));
    }

    onMessage(event) {
        console.log(this.requestsIds, event);
        var response = JSON.parse(event.data);
        if (response.id) {
            var requestExecutor = this.requests[response.id];
            requestExecutor.resolveFunction(response);
        }
    }

    getNextRequestId() {
        return ++this.requestsIds;
    }

    async sendRequest(request) {
        request.id = this.getNextRequestId();
        var requestExecutor = new WebSocketRequestExecutor(request);
        this.requests[request.id] = requestExecutor;

        this.webSocketConnection.send(JSON.stringify(request));

        return new Promise(function (resolve, reject) {
            requestExecutor.executor(resolve, reject);
        });
    }
}

export default WebSocketClient;

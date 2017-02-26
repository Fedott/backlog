export default class Request {
    id: number;
    type: string;
    payload;

    constructor(type: string, payload) {
        this.type = type;
        this.payload = payload;
    }
}
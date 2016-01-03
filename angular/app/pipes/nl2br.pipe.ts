import {Pipe} from "angular2/core";
import {PipeTransform} from "angular2/core";

@Pipe({name: 'nl2br'})
export class Nl2BrPipe implements PipeTransform {
    transform(value:any, args:any[]):any {
        if (null == value) {
            return null;
        }
        return value.replace(/\n/g, '<br/>');
    }
}

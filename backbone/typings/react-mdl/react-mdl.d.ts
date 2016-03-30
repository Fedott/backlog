declare namespace __ReactMDL {
    import Component = __React.Component;

    export class List extends Component<any, any> {}
    export class ListItem extends Component<any, any> {}
    export class ListItemContent extends Component<any, any> {}
    export class ListItemAction extends Component<any, any> {}
    export class Checkbox extends Component<any, any> {}
    export class Switch extends Component<any, any> {}
    export class Textfield extends Component<any, any> {}
    export class IconToggle extends Component<any, any> {}
    export class IconButton extends Component<any, any> {}
    export class Icon extends Component<any, any> {}
    export class Button extends Component<__React.HTMLProps<any>, any> {}
    export class FABButton extends Button {}
    export class Dialog extends Component<any, any> {}
    export class DialogTitle extends Component<any, any> {}
    export class DialogContent extends Component<any, any> {}
    export class DialogActions extends Component<any, any> {}
    export class Card extends Component<any, any> {}
    export class CardTitle extends Component<any, any> {}
    export class CardText extends Component<any, any> {}
    export class CardActions extends Component<any, any> {}
    export class CardMenu extends Component<any, any> {}

    export class Layout extends Component<any, any> {}
    export class Header extends Component<any, any> {}
    export class HeaderRow extends Component<any, any> {}
    export class Navigation extends Component<any, any> {}
    export class Drawer extends Component<any, any> {}
    export class Content extends Component<any, any> {}
}

declare module 'react-mdl' {
    export = __ReactMDL;
}
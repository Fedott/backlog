import * as React from "react";
import * as ReactDOM from "react-dom";
import StoryView from "./StoryView.jsx";
import StoryEditForm from "./StoryEditFrom.jsx";

class StoryItem extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        edit: React.PropTypes.bool,
        isCreateForm: React.PropTypes.bool,
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            story: props.story,
            edit: props.edit || false,
            isCreateForm: props.isCreateForm || false,
            isDeleted: false,
        };
    }

    onChangeEdit() {
        this.setState({
            edit: !this.state.edit,
        })
    }

    onSaved(story) {
        this.setState({
            story: story,
            edit: false,
            isCreateForm: false,
        })
    }

    onDeleted() {
        this.setState({
            isDeleted: true,
        });
    }

    render() {
        if (this.state.isDeleted) {
            return null;
        }

        if (this.state.edit) {
            return <StoryEditForm
                story={this.state.story}
                onCancel={this.onChangeEdit.bind(this)}
                onSaved={this.onSaved.bind(this)}
                isCreateForm={this.state.isCreateForm}
            />;
        } else {
            return <StoryView
                story={this.state.story}
                onChangeEdit={this.onChangeEdit.bind(this)}
                onDeleted={this.onDeleted.bind(this)}
            />;
        }
    }
}

export default StoryItem;

/// <reference types="mithril" />
import Widget, { WidgetAttrs } from 'flarum/extensions/afrux-forum-widgets-core/common/components/Widget';
export default class OnlineGuestsWidget<T extends WidgetAttrs> extends Widget<T> {
    className(): string;
    icon(): string;
    title(): string;
    content(): JSX.Element;
}

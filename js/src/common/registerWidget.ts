import Widgets from 'flarum/extensions/afrux-forum-widgets-core/common/extend/Widgets';

import OnlineGuestsWidget from './components/OnlineGuestsWidget';

export default function (app: any) {
  new Widgets()
    .add({
      key: 'onlineGuests',
      component: OnlineGuestsWidget,
      isDisabled: () => app.forum.attribute('onlineGuests') === undefined,
      isUnique: true,
      placement: 'end',
      position: 2,
    })
    .extend(app, 'ianm-online-guests');
}

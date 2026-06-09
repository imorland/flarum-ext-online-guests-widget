import app from 'flarum/admin/app';
import registerWidget from '../common/registerWidget';

export { default as extend } from './extend';

app.initializers.add('ianm/online-guests', () => {
  registerWidget(app);
});

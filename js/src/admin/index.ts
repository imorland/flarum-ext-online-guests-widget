import app from 'flarum/admin/app';
import registerWidget from '../common/registerWidget';

app.initializers.add('ianm/online-guests', () => {
  registerWidget(app);

  app.extensionData
    .for('ianm-online-guests')
    .registerPermission(
      {
        permission: 'viewOnlineGuests',
        icon: 'fas fa-eye',
        label: app.translator.trans('ianm-online-guests.admin.permissions.view_online_guests_label'),
        allowGuest: true,
      },
      'view'
    )
    .registerSetting({
      setting: 'ianm-online-guests.online-duration',
      label: app.translator.trans('ianm-online-guests.admin.settings.online_duration_label'),
      type: 'number',
      min: 0,
    })
    .registerSetting({
      setting: 'ianm-online-guests.cache-duration',
      label: app.translator.trans('ianm-online-guests.admin.settings.cache_duration_label'),
      type: 'number',
      min: 0,
    });
});

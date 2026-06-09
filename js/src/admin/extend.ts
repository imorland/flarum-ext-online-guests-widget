import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';

export default [
  new Extend.Admin()
    .permission(
      () => ({
        permission: 'viewOnlineGuests',
        icon: 'fas fa-eye',
        label: app.translator.trans('ianm-online-guests.admin.permissions.view_online_guests_label', {}, true),
        allowGuest: true,
      }),
      'view'
    )
    .setting(() => ({
      setting: 'ianm-online-guests.online-duration',
      label: app.translator.trans('ianm-online-guests.admin.settings.online_duration_label', {}, true),
      type: 'number',
      min: 0,
    }))
    .setting(() => ({
      setting: 'ianm-online-guests.cache-duration',
      label: app.translator.trans('ianm-online-guests.admin.settings.cache_duration_label', {}, true),
      type: 'number',
      min: 0,
    })),
];

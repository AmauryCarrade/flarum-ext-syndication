import SettingsModal from "@fof/components/admin/settings/SettingsModal";
import BooleanItem from '@fof/components/admin/settings/items/BooleanItem';
import IntegerItem from "@fof/components/admin/settings/items/NumberItem";

app.initializers.add('amaurycarrade-syndication', () => {
  app.extensionSettings['amaurycarrade-syndication'] = () => app.modal.show(new SettingsModal({
    title: app.translator.trans('amaurycarrade-syndication.admin.settings.title'),
    className: 'SyndicationSettingsModal',
    items: [
      <BooleanItem key={"amaurycarrade-syndication.plugin.full-text"}>
        {app.translator.trans("amaurycarrade-syndication.admin.settings.full-text.label")}
        <span>{app.translator.trans("amaurycarrade-syndication.admin.settings.full-text.help")}</span>
        <span>{app.translator.trans("amaurycarrade-syndication.admin.settings.full-text.recommendation")}</span>
      </BooleanItem>,

      <BooleanItem key={"amaurycarrade-syndication.plugin.html"}>
        {app.translator.trans("amaurycarrade-syndication.admin.settings.html.label")}
        <span>{app.translator.trans("amaurycarrade-syndication.admin.settings.html.help")}</span>
      </BooleanItem>,

      <IntegerItem key={"amaurycarrade-syndication.plugin.entries-count"}>
        {app.translator.trans("amaurycarrade-syndication.admin.settings.entries-count")}
      </IntegerItem>,
    ]
  }));
});

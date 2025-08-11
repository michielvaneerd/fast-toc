import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { CheckboxControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

const PluginDocumentSettingPanelDemo = () => {

    const {showToc} = useSelect((select) => {
        const value = select("core/editor").getEditedPostAttribute("meta")["fast_toc_show_toc"];
        return {
            showToc: value !== "" ? (value === "true") : (window.FAST_TOC_ENABLED == 1)
        };
    }, []);

    const { editPost } = useDispatch('core/editor');
    const onTocChange = useCallback((newValue) => {
        editPost({
            meta: {
                fast_toc_show_toc: newValue ? "true" : "false"
            }
        });
      }, [editPost]);

    return <PluginDocumentSettingPanel
        title="Fast TOC">
        <CheckboxControl label="Enable Fast TOC" onChange={onTocChange} checked={showToc} />
    </PluginDocumentSettingPanel>;
};

registerPlugin('my-plugin-sidebar', {render: PluginDocumentSettingPanelDemo, icon: ''});

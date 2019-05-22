import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';
import ShortcodeSerialiser from 'lib/ShortcodeSerialiser';
import InsertShortCodeModal from 'components/InsertShortcodeModal';
import i18n from 'i18n';


const InjectableInsertShortcodeModal = loadComponent(InsertShortCodeModal);
const filter = 'div[data-shortcode="embed"]';

(() => {
    const ssembed = {
        init: (editor, url) => {
            editor.addButton('shortcodable', {
                title: "Short Code",
                image: url.replace('dist', '') + 'images/shortcodable.png',
                cmd: 'shortcodable',
            });

            editor.addCommand('shortcodable', () => {
                // See HtmlEditorField.js
                $(`#${editor.id}`).entwine('ss').openShortCodeDialog();
            });

            // Replace the tinymce default media commands with the ssembed command
            editor.on('BeforeExecCommand', (e) => {
                const cmd = e.command;
                const ui = e.ui;
                const val = e.value;
            });

            editor.on('SaveContent', (o) => {
                const content = $(`<div>${o.content}</div>`);

                o.content = content.html();
            });
            editor.on('BeforeSetContent', (o) => {
                let content = o.content;

                o.content = content;
            });
        },

    };

    tinymce.PluginManager.add('shortcodable', (editor, url) => ssembed.init(editor, url));
})();


$.entwine('ss', ($) => {
    let dialog = null;

    $('select.shortcode-type').entwine({
        onchange: function(){
            $('.js-injector-boot #insert-shortcode-react__dialog-wrapper').reRender($(this).val());
        }
    });

    $('textarea.htmleditor').entwine({
        openShortCodeDialog: function() {
            dialog = $('#insert-shortcode-react__dialog-wrapper');

            if (!dialog.length) {
                dialog = $('<div id="insert-shortcode-react__dialog-wrapper" />');
                $('body').append(dialog);
            }

            dialog.setElement(this);
            dialog.open();
            return;
        },
    });
    $('.js-injector-boot #insert-shortcode-react__dialog-wrapper').entwine({
        Element: null,

        Data: {},

        onunmatch() {
            // solves errors given by ReactDOM "no matched root found" error.
            this._clearModal();
        },

        _clearModal() {
            ReactDOM.unmountComponentAtNode(this[0]);
            // this.empty();
        },

        open() {
            this._renderModal(true);
        },

        close() {
            this.setData({});
            this._renderModal(false);
        },

        reRender(val) {
            this._clearModal();
            this._renderModal(true, val);
        },

        /**
         * Renders the react modal component
         *
         * @param {boolean} isOpen
         * @param {string} type
         * @private
         */
        _renderModal(isOpen, type) {
            const attrs = this.getOriginalAttributes();
            if (attrs && !type) {
                type = attrs.type;
            }

            const handleHide = () => this.close();
            // Inserts embed into page
            const handleInsert = (...args) => this._handleInsert(...args);
            // Create edit form from url
            const handleLoadingError = (...args) => this._handleLoadingError(...args);

            // create/update the react component
            ReactDOM.render(
                <InjectableInsertShortcodeModal
                    isOpen={isOpen}
                    onInsert={handleInsert}
                    shortCodeClass={type}
                    onClosed={handleHide}
                    onLoadingError={handleLoadingError}
                    shortcodeAttributes={attrs}
                    bodyClassName="modal__dialog"
                    className="insert-shortcode-react__dialog-wrapper"
                />,
                this[0]
            );
        },

        _handleLoadingError() {
            this.setData({});
            this.open();
        },

        /**
         * Handles inserting the selected file in the modal
         *
         * @param {object} data
         * @returns {Promise}
         * @private
         */
        _handleInsert(data) {
            this.setData(data);
            this.insertsShortcode();
            this.close();
        },

        _handleCreate(data) {
            this.setData(Object.assign({}, this.getData(), data));
            this.open();
        },

        insertsShortcode() {
            const $field = this.getElement();
            if (!$field) {
                return false;
            }
            const editor = $field.getEditor();
            if (!editor) {
                return false;
            }

            const shortCode = this.getHTML();
            const attrs = this.getOriginalAttributes();
            const node = $(editor.getSelectedNode());

            tinymce.activeEditor.selection.setContent(shortCode);

            editor.addUndo();
            editor.repaint();

            return true;

        },

        getHTML: function(){
            const data = this.getData();
            const type = data.ShortcodeType;
            let html = type + ' type="'+ data.ShortcodeClass + '"';

            delete(data.SecurityID);
            delete(data.ShortcodeType);
            delete(data.ShortcodeClass);
            delete(data.action_addshortcode);

            for (let key in data) {
                html += ' ' + key + '="' + data[key] + '"';
            }
            console.log('here');

            return "[" + html + "][/"+ type +"]";
        },

        getOriginalAttributes() {
            const $field = this.getElement();
            if (!$field) {
                return {};
            }

            const node = $field.getEditor().getSelectedNode();
            if (!node) {
                return {};
            }
            const $node = $(node);

            const attributes = {};
            const matches = $node.text().match(/[\w-]+=".+?"/g);
            if (matches) {
                matches.forEach(function(attribute) {
                    attribute = attribute.match(/([\w-]+)="(.+?)"/);
                    attributes[attribute[1]] = attribute[2];
                });
            }

            return attributes;
        },
    });

});

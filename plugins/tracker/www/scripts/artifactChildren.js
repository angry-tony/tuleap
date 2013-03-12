/**
  * Copyright (c) Enalean, 2013. All rights reserved
  *
  * This file is a part of Tuleap.
  *
  * Tuleap is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * Tuleap is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with Tuleap. If not, see <http://www.gnu.org/licenses/
  */

var tuleap = tuleap || { };
tuleap.artifact = tuleap.artifact || { };

tuleap.artifact.HierarchyViewer = Class.create({

    initialize : function(base_url, container) {
        this.base_url = base_url;
        this.container = container;
    },

    getArtifactChildren : function(artifact_id) {
        new Ajax.Request( this.base_url, {
            method : 'GET',
            parameters : {
                aid : artifact_id,
                func : 'get-children'
            },
            onSuccess : this.receiveChildren.bind(this)
        });
    },

    receiveChildren: function (transport) {
        var children = transport.responseJSON,
            tbody;

        if (! children.length) {
            this.displaysNoChild();
            return;
        }

        this.insertTable();
        tbody = this.container.down('tbody');

        children.map(function (child) {
            this.insertChild(tbody, child);
        }.bind(this));
    },

    displaysNoChild: function () {
        this.container.insert('<em>There is not any children yet</em>');
    },

    insertTable: function () {
        this.container.insert('<table class="tree-view"> \
                <thead> \
                    <tr class="boxtable"> \
                        <th class="boxtitle"></th> \
                        <th class="boxtitle">Title</th> \
                        <th class="boxtitle">Status</th> \
                    </tr> \
                </thead> \
                <tbody> \
                </tbody> \
            </table>');
    },

    insertChild: function (tbody, child) {
        var template = new Template('<tr> \
                <td><a href="#{url}">#{xref}</a></td> \
                <td>#{title}</td> \
                <td>#{status}</td> \
            </tr>')

        tbody.insert(template.evaluate(child));
    }
});

//var base_url = codendi.tracker.base_url;
//console.log(base_url);
//var hv =  new tuleap.artifact.HierarchyViewer(base_url);
//
//hv.getArtifactChildren(16);
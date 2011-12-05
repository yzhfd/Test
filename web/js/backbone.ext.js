{
	_.extend(Backbone.Model.prototype, {
		remoteArributes: {},
		isOutOfSync: function () {
			// no compare index
			var remoteAttrs = $.extend(true, {}, this.remoteAttributes);
			remoteAttrs.index = this.attributes.index;
			return !_.isEqual(this.attributes, remoteAttrs);
		},
		synced: function () {
			this.remoteAttributes = $.extend(true, {}, this.attributes);
		}
	});
}
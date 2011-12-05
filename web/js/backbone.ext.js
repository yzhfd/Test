{
	_.extend(Backbone.Model.prototype, {
		remoteArributes: {},
		isOutOfSync: function () {
			return !_.isEqual(this.attributes, this.remoteAttributes);
		},
		synced: function () {
			this.remoteAttributes = $.extend(true, {}, this.attributes);
		}
	});
}
/**
 * UndoManager
 */
window.UndoManager = function (models) {
	this.models = models;
	
	var close = _.bind(function (e) {
		var models = _.toArray(this.recycleBin);
		while (models.length > 0) {
			var model = models.pop();
			model.destroy();
		}
		this.recycleBin = {};
	}, this);
	$(window).unload(close);
	//$(window).bind('beforeunload', close);
	
	models.bind('add', this._add, this);
	models.bind('remove', this._remove, this);
	models.bind('change', this._change, this);
};

_.extend(UndoManager.prototype, {
	pointer: -1,
	states: [],
	prevOpTime: 0,
	undoTime: 0,
	recycleBin: {},
	_save: function (obj) {
		var now = new Date().getTime();
		// triggered by undo/redo
		if (now - this.undoTime < 40) {
			// still in undo
			// delete multiple models will trigger this multiple times
			this.undoTime = now;
			return;
		}
		
		if (now - this.prevOpTime < 20) { // in milliseconds
			// this action takes place at the same time as the previous one
			var objs = _.last(this.states);
			objs.push(obj);
		} else {
			++this.pointer;
			while (this.pointer < this.states.length) {
				this.states.pop();
			}
			this.states.push([obj]);
		}
		this.prevOpTime = now;
	},
	_add: function (model) {
		this._save({
			id: null, // model.id is undefined
			cid: model.cid,
			attrs: model.attributes
		});
	},
	_remove: function (model) {
		this.recycleBin[model.cid] = model;
		this._save({
			id: model.id,
			cid: model.cid,
			attrs: model.attributes
		});
	},
	_change: function (model) {
		this._save({
			id: model.id,
			cid: model.cid,
			attrs: model.previousAttributes()
		});
	},
	_do: function () {
		var objs = this.states[this.pointer];
		_.each(objs, function (obj) {
			// undo/redo is like swap between things on stage and in undo stack
			// think this way will help you understand the code
			var model = this.models.getByCid(obj.cid);
			if (model) {
				if (obj.id) {
					var attrs = $.extend(true, {}, model.attributes); // deep copy
					model.set(obj.attrs);
					obj.attrs = attrs;
					model.save();
				} else {
					obj.id = model.id;
					this.models.remove(model);
					this.recycleBin[model.cid] = model;
				}
			} else {
				var model = this.recycleBin[obj.cid];
				delete this.recycleBin[obj.cid];
				model = this.models.add(model);
				obj.id = null;
			}
		}, this);
	},
	undo: function () {
		if (this.pointer < 0) {
			return;
		}
		
		this.undoTime = new Date().getTime();
		this._do();		
		--this.pointer;
	},
	// redo is actually reversed undo
	redo: function () {
		if (this.pointer+1 >= this.states.length) {
			return;
		}
		++this.pointer;
		
		this.undoTime = new Date().getTime();
		this._do();
	}
});
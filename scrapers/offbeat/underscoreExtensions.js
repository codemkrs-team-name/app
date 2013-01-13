var _ = require('underscore')
		;
_.mixin({
	// Create object from a callback that returns [name, value] pairs. We use this combination a lot
	// so might as well bake it into the framework
	 mapObject: _.compose(_.object, _.map)

	 // Create a new object by removing keys from object1 which also exist in object2
	,differenceObject: function(obj1, obj2) {		
          return _.pick(obj1,  _.difference(_.keys(obj1), _.keys(obj2)) );
	}

	// Shortcut similar to _.find(collection, function(){ return x.SlideId == val})
	// or _.where(collection, {SlideId: val})[0]
	// Usage:
	//	var slide3 = _.findBy(entities, 3, 'SlideId');
	,findBy: function (collection, val, prop) {
		return _.find(collection, function findBy(x) { return x&&(x[prop] === val) });
	}

	// Pluck values out of an array of objects.
	// Usage:		
	//	arr = [	 {a:'a1', b: 'b1', c: 'c1'}
	//			,{a:'a2', b: 'b2', c: 'c2'} ];
	//		_.pluckValues(arr, 'b', 'c') 
	//	or 	_.pluckValues(arr, ['b', 'c']); =>
	//										[	 {b: 'b1', c: 'c1'}
	//											,{b: 'b2', c: 'c2'} ];
	,pluckValues: function(obj, properties) {
		properties = _.flatten(_.tail(arguments));
		if(_.isArray(obj))
			return _.map(obj, function(val){ return _.pick(val, properties)});
		return _.mapObject(obj, function(val, key){
			return [key, _.pick(val, properties)];
		});
	}

	// return the array itself but with any functions replaced by the results of invoking them
	,resultAll: function(collection) {
		var args = _.tail(arguments)
		return _.map(collection, function(v) {
			return _.isFunction(v) ? v.apply(undefined, _.tail(args)) : v 
		})
	}

	//tap each item in the collection returning the collection itself
	,tapEach: function(collection, fn, bindTo) {
		_.each(collection, fn, bindTo);
		return collection;
	}

	//Throw an error when the given keys do not belong to the object.
	//Useful for explicitly stating and checking non-optional keys in options objects
	//Usage:
	// _.ensureHasKeys(this.options, 'mode', 'language');
	,ensureHasKeys: function(obj) {
		var keys = _.tail(arguments);
		_.each(keys, function(key) {
			if (!_.has(obj, key))
			  throw "Expected object to contain " + key;
		});
	}

	//union while flattening through any nested arrays
	,unionAll: _.compose(_.flatten, _.union)

	//Takes a filterFunction and an execution function. Returns a new method that when 
	//run will only trigger the exection function if the filter function returns truthy.
	,runWhen: function(fnFilter, fn) {
		return function runWhen(){
			if(fnFilter.apply(this, arguments))
				fn.apply(this, arguments);
		}
	}
});

_.mixin({
	//Visitor pattern for navigating tree-like structures assumes a default children property 
	//named 'children', otherwise, a string or function can be provided
	 visit: function(root, callback, childProperty) {
		if(null == root) return;
		var getChildren = getChildrenFn(childProperty);
		callback(root)
		_.each(getChildren(root), function(child){
			_.visit(child, callback, getChildren);
		})
	}

	//Visits all nodes in a tree-line structure and returns all nodes that match a filter test function
	,collectTree: function(root, test, childProperty) {
		var result = [];
		function testNode(node) { 
			test(node) && result.push(node);
		}
		_.visit(root, testNode, childProperty);
		return result;
	}

	//Returns all nodes from a tree-line structure
	,collectTreeNodes: function(root, childProperty) {
		return _.collectTree(root, function(){ return true }, childProperty)
	}
});

function getChildrenFn(prop) {
	return _.isFunction(prop) ? prop : 
			(function getChildrenFn(x){ return x[prop|| 'children']});
}

module.exports = _;
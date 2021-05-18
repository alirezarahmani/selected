/*!
FullCalendar Resources Common Plugin v4.0.2
Docs & License: https://fullcalendar.io/scheduler
(c) 2019 Adam Shaw
*/
! function(e, r) {
    "object" == typeof exports && "undefined" != typeof module ? r(exports, require("@fullcalendar/core")) : "function" == typeof define && define.amd ? define(["exports", "@fullcalendar/core"], r) : (e = e || self, r(e.FullCalendarResourceCommon = {}, e.FullCalendar))
}(this, function(e, r) {
    "use strict";

    function t(e, r) {
        function t() {
            this.constructor = e
        }
        oe(e, r), e.prototype = null === r ? Object.create(r) : (t.prototype = r.prototype, new t)
    }

    function n(e, t, n, i) {
        if (t) {
            var a = o(n.instances, i),
                c = s(a, n.defs);
            return se(c, u(c, e)), r.filterHash(e, function(e, r) {
                return c[r]
            })
        }
        return e
    }

    function o(e, t) {
        return r.filterHash(e, function(e) {
            return r.rangesIntersect(e.range, t)
        })
    }

    function s(e, r) {
        var t = {};
        for (var n in e)
            for (var o = e[n], s = 0, u = r[o.defId].resourceIds; s < u.length; s++) {
                var i = u[s];
                t[i] = !0
            }
        return t
    }

    function u(e, r) {
        var t = {};
        for (var n in e)
            for (var o = void 0;
                (o = r[n]) && (n = o.parentId);) t[n] = !0;
        return t
    }

    function i(e) {
        return r.mapHash(e, function(e) {
            return e.ui
        })
    }

    function a(e, t, n) {
        return r.mapHash(e, function(e, r) {
            return r ? c(e, t[r], n) : e
        })
    }

    function c(e, t, n) {
        for (var o = [], s = 0, u = t.resourceIds; s < u.length; s++) {
            var i = u[s];
            n[i] && o.unshift(n[i])
        }
        return o.unshift(e), r.combineEventUis(o)
    }

    function l(e) {
        ce.push(e)
    }

    function d(e) {
        return ce[e]
    }

    function f(e) {
        return Boolean(ce[e.sourceDefId].ignoreRange)
    }

    function p(e) {
        for (var t = ce.length - 1; t >= 0; t--) {
            var n = ce[t],
                o = n.parseMeta(e);
            if (o) {
                var s = h("object" == typeof e && e ? e : {}, o, t);
                return s._raw = r.freezeRaw(e), s
            }
        }
        return null
    }

    function h(e, t, n) {
        var o = r.refineProps(e, ae);
        return o.sourceId = String(le++), o.sourceDefId = n, o.meta = t, o.publicId = o.id, o.isFetching = !1, o.latestFetchId = "", o.fetchRange = null, delete o.id, o
    }

    function v(e, r, t, n) {
        switch (r.type) {
            case "INIT":
                return g(n.opt("resources"), n);
            case "RESET_RESOURCE_SOURCE":
                return g(r.resourceSourceInput, n, !0);
            case "PREV":
            case "NEXT":
            case "SET_DATE":
            case "SET_VIEW_TYPE":
                return R(e, t.activeRange, n);
            case "RECEIVE_RESOURCES":
            case "RECEIVE_RESOURCE_ERROR":
                return E(e, r.fetchId, r.fetchRange);
            case "REFETCH_RESOURCES":
                return y(e, t.activeRange, n);
            default:
                return e
        }
    }

    function g(e, r, t) {
        if (e) {
            var n = p(e);
            return !t && r.opt("refetchResourcesOnNavigate") || (n = y(n, null, r)), n
        }
        return null
    }

    function R(e, t, n) {
        return !n.opt("refetchResourcesOnNavigate") || f(e) || e.fetchRange && r.rangesEqual(e.fetchRange, t) ? e : y(e, t, n)
    }

    function y(e, r, t) {
        var n = d(e.sourceDefId),
            o = String(de++);
        return n.fetch({
            resourceSource: e,
            calendar: t,
            range: r
        }, function(e) {
            t.afterSizingTriggers._resourcesRendered = [null], t.dispatch({
                type: "RECEIVE_RESOURCES",
                fetchId: o,
                fetchRange: r,
                rawResources: e.rawResources
            })
        }, function(e) {
            t.dispatch({
                type: "RECEIVE_RESOURCE_ERROR",
                fetchId: o,
                fetchRange: r,
                error: e
            })
        }), se({}, e, {
            isFetching: !0,
            latestFetchId: o
        })
    }

    function E(e, r, t) {
        return r === e.latestFetchId ? se({}, e, {
            isFetching: !1,
            fetchRange: t
        }) : e
    }

    function m(e, t, n, o) {
        void 0 === t && (t = "");
        var s = {},
            u = r.refineProps(e, fe, {}, s),
            i = {},
            a = r.processScopedUiProps("event", s, o, i);
        if (u.id || (u.id = pe + he++), u.parentId || (u.parentId = t), u.businessHours = u.businessHours ? r.parseBusinessHours(u.businessHours, o) : null, u.ui = a, u.extendedProps = se({}, i, u.extendedProps), Object.freeze(a.classNames), Object.freeze(u.extendedProps), n[u.id]);
        else if (n[u.id] = u, u.children) {
            for (var c = 0, l = u.children; c < l.length; c++) {
                var d = l[c];
                m(d, u.id, n, o)
            }
            delete u.children
        }
        return u
    }

    function S(e) {
        return 0 === e.indexOf(pe) ? "" : e
    }

    function I(e, t, n, o) {
        switch (t.type) {
            case "INIT":
                return {};
            case "RECEIVE_RESOURCES":
                return b(e, t.rawResources, t.fetchId, n, o);
            case "ADD_RESOURCE":
                return C(e, t.resourceHash);
            case "REMOVE_RESOURCE":
                return _(e, t.resourceId);
            case "SET_RESOURCE_PROP":
                return w(e, t.resourceId, t.propName, t.propValue);
            case "RESET_RESOURCES":
                return r.mapHash(e, function(e) {
                    return se({}, e)
                });
            default:
                return e
        }
    }

    function b(e, r, t, n, o) {
        if (n.latestFetchId === t) {
            for (var s = {}, u = 0, i = r; u < i.length; u++) {
                var a = i[u];
                m(a, "", s, o)
            }
            return s
        }
        return e
    }

    function C(e, r) {
        return se({}, e, r)
    }

    function _(e, r) {
        var t = se({}, e);
        delete t[r];
        for (var n in t) t[n].parentId === r && (t[n] = se({}, t[n], {
            parentId: ""
        }));
        return t
    }

    function w(e, r, t, n) {
        var o, s, u = e[r];
        return u ? se({}, e, (o = {}, o[r] = se({}, u, (s = {}, s[t] = n, s)), o)) : e
    }

    function O(e, r) {
        var t;
        switch (r.type) {
            case "INIT":
                return {};
            case "SET_RESOURCE_ENTITY_EXPANDED":
                return se({}, e, (t = {}, t[r.id] = r.isExpanded, t));
            default:
                return e
        }
    }

    function P(e, r, t) {
        var n = v(e.resourceSource, r, e.dateProfile, t),
            o = I(e.resourceStore, r, n, t),
            s = O(e.resourceEntityExpansions, r);
        return se({}, e, {
            resourceSource: n,
            resourceStore: o,
            resourceEntityExpansions: s
        })
    }

    function T(e, t, n) {
        var o = r.refineProps(t, ve, {}, n),
            s = o.resourceIds;
        o.resourceId && s.push(o.resourceId), e.resourceIds = s, e.resourceEditable = o.resourceEditable
    }

    function x(e, r, t) {
        var n = r.dateSpan.resourceId,
            o = t.dateSpan.resourceId;
        n && o && n !== o && (e.resourceMutation = {
            matchResourceId: n,
            setResourceId: o
        })
    }

    function D(e, r, t) {
        var n = r.resourceMutation;
        if (n && j(e, t)) {
            var o = e.resourceIds.indexOf(n.matchResourceId);
            if (o !== -1) {
                var s = e.resourceIds.slice();
                s.splice(o, 1), s.indexOf(n.setResourceId) === -1 && s.push(n.setResourceId), e.resourceIds = s
            }
        }
    }

    function j(e, r) {
        var t = e.resourceEditable;
        if (null == t) {
            var n = e.sourceId && r.state.eventSources[e.sourceId];
            n && (t = n.extendedProps.resourceEditable), null == t && (t = r.opt("eventResourceEditable"), null == t && (t = r.opt("editable")))
        }
        return t
    }

    function F(e, r) {
        var t = e.resourceMutation;
        return t ? {
            oldResource: r.getResourceById(t.matchResourceId),
            newResource: r.getResourceById(t.setResourceId)
        } : {
            oldResource: null,
            newResource: null
        }
    }

    function U(e, r) {
        var t = e.dateSpan.resourceId,
            n = r.dateSpan.resourceId;
        if (t && n) return (e.component.allowAcrossResources !== !1 || t === n) && {
            resourceId: t
        }
    }

    function A(e, r) {
        return e.resourceId ? {
            resource: r.getResourceById(e.resourceId)
        } : {}
    }

    function H(e, r) {
        return e.resourceId ? {
            resource: r.getResourceById(e.resourceId)
        } : {}
    }

    function B(e, t) {
        var n = new Re,
            o = n.splitProps(se({}, e, {
                resourceStore: t.state.resourceStore
            }));
        for (var s in o) {
            var u = o[s];
            if (s && o[""] && (u = se({}, u, {
                    eventStore: r.mergeEventStores(o[""].eventStore, u.eventStore),
                    eventUiBases: se({}, o[""].eventUiBases, u.eventUiBases)
                })), !r.isPropsValid(u, t, {
                    resourceId: s
                }, z.bind(null, s))) return !1
        }
        return !0
    }

    function z(e, r) {
        return se({}, r, {
            constraints: M(e, r.constraints)
        })
    }

    function M(e, r) {
        return r.map(function(r) {
            var t = r.defs;
            if (t)
                for (var n in t) {
                    var o = t[n].resourceIds;
                    if (o.length && o.indexOf(e) === -1) return !1
                }
            return r
        })
    }

    function V(e) {
        return e.resourceId ? {
            resourceId: e.resourceId
        } : {}
    }

    function N(e, r) {
        var t = e.component;
        if (t.allowAcrossResources === !1 && e.dateSpan.resourceId !== r.dateSpan.resourceId) return !1
    }

    function k(e, t) {
        var n = t.opt("schedulerLicenseKey");
        q(window.location.href) || K(n) || r.appendToElement(e, '<div class="fc-license-message" style="' + r.htmlEscape(r.cssToStr(Ie)) + '">Please use a valid license key. <a href="' + me + '">More Info</a></div>')
    }

    function K(e) {
        if (Se.indexOf(e) !== -1) return !0;
        var t = (e || "").match(/^(\d+)\-fcs\-(\d+)$/);
        if (t && 10 === t[1].length) {
            var n = new Date(1e3 * parseInt(t[2], 10)),
                o = new Date(r.config.mockSchedulerReleaseDate || ye);
            if (r.isValidDate(o)) {
                var s = r.addDays(o, -Ee);
                if (s < n) return !0
            }
        }
        return !1
    }

    function q(e) {
        return /\w+\:\/\/fullcalendar\.io\/|\/demos\/[\w-]+\.html$/.test(e)
    }

    function G(e, t) {
        var n = t.state.resourceSource._raw;
        r.isValuesSimilar(n, e, 2) || t.dispatch({
            type: "RESET_RESOURCE_SOURCE",
            resourceSourceInput: e
        })
    }

    function L(e, r, t) {
        var n, o, s, u, i = t.dateEnv,
            a = {};
        return r && (n = t.opt("startParam"), o = t.opt("endParam"), s = t.opt("timeZoneParam"), a[n] = i.formatIso(r.start), a[o] = i.formatIso(r.end), "local" !== i.timeZone && (a[s] = i.timeZone)), u = "function" == typeof e.extraParams ? e.extraParams() : e.extraParams || {}, se(a, u), a
    }

    function Z(e, r) {
        return "function" == typeof e ? function(t) {
            return e(new ge(r, t))
        } : function(e) {
            return e.title || S(e.id)
        }
    }

    function J(e, r) {
        return W(e, [], r, !1, {}, !0).map(function(e) {
            return e.resource
        })
    }

    function W(e, r, t, n, o, s) {
        var u = Y(e, n ? -1 : 1, r, t),
            i = [];
        return X(u, i, n, [], 0, o, s), i
    }

    function X(e, r, t, n, o, s, u) {
        for (var i = 0; i < e.length; i++) {
            var a = e[i],
                c = a.group;
            if (c)
                if (t) {
                    var l = r.length,
                        d = n.length;
                    if (X(a.children, r, t, n.concat(0), o, s, u), l < r.length) {
                        var f = r[l],
                            p = f.rowSpans = f.rowSpans.slice();
                        p[d] = r.length - l
                    }
                } else {
                    var h = c.spec.field + ":" + c.value,
                        v = null != s[h] ? s[h] : u;
                    r.push({
                        id: h,
                        group: c,
                        isExpanded: v
                    }), v && X(a.children, r, t, n, o + 1, s, u)
                } else if (a.resource) {
                var h = a.resource.id,
                    v = null != s[h] ? s[h] : u;
                r.push({
                    id: h,
                    rowSpans: n,
                    depth: o,
                    isExpanded: v,
                    hasChildren: Boolean(a.children.length),
                    resource: a.resource,
                    resourceFields: a.resourceFields
                }), v && X(a.children, r, t, n, o + 1, s, u)
            }
        }
    }

    function Y(e, r, t, n) {
        var o = $(e, n),
            s = [];
        for (var u in o) {
            var i = o[u];
            i.resource.parentId || Q(i, s, t, 0, r, n)
        }
        return s
    }

    function $(e, r) {
        var t = {};
        for (var n in e) {
            var o = e[n];
            t[n] = {
                resource: o,
                resourceFields: te(o),
                children: []
            }
        }
        for (var n in e) {
            var o = e[n];
            if (o.parentId) {
                var s = t[o.parentId];
                s && re(t[n], s.children, r)
            }
        }
        return t
    }

    function Q(e, r, t, n, o, s) {
        if (t.length && (o === -1 || n <= o)) {
            var u = ee(e, r, t[0]);
            Q(e, u.children, t.slice(1), n + 1, o, s)
        } else re(e, r, s)
    }

    function ee(e, t, n) {
        var o, s, u = e.resourceFields[n.field];
        if (n.order)
            for (s = 0; s < t.length; s++) {
                var i = t[s];
                if (i.group) {
                    var a = r.flexibleCompare(u, i.group.value) * n.order;
                    if (0 === a) {
                        o = i;
                        break
                    }
                    if (a < 0) break
                }
            } else
                for (s = 0; s < t.length; s++) {
                    var i = t[s];
                    if (i.group && u === i.group.value) {
                        o = i;
                        break
                    }
                }
        return o || (o = {
            group: {
                value: u,
                spec: n
            },
            children: []
        }, t.splice(s, 0, o)), o
    }

    function re(e, t, n) {
        var o;
        for (o = 0; o < t.length; o++) {
            var s = r.compareByFieldSpecs(t[o].resourceFields, e.resourceFields, n);
            if (s > 0) break
        }
        t.splice(o, 0, e)
    }

    function te(e) {
        var r = se({}, e.extendedProps, e.ui, e);
        return delete r.ui, delete r.extendedProps, r
    }

    function ne(e, r) {
        return e.spec === r.spec && e.value === r.value
    }
    /*! *****************************************************************************
        Copyright (c) Microsoft Corporation. All rights reserved.
        Licensed under the Apache License, Version 2.0 (the "License"); you may not use
        this file except in compliance with the License. You may obtain a copy of the
        License at http://www.apache.org/licenses/LICENSE-2.0

        THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
        KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
        WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
        MERCHANTABLITY OR NON-INFRINGEMENT.

        See the Apache Version 2.0 License for specific language governing permissions
        and limitations under the License.
        ***************************************************************************** */
    var oe = function(e, r) {
            return (oe = Object.setPrototypeOf || {
                    __proto__: []
                }
                instanceof Array && function(e, r) {
                    e.__proto__ = r
                } || function(e, r) {
                    for (var t in r) r.hasOwnProperty(t) && (e[t] = r[t])
                })(e, r)
        },
        se = function() {
            return se = Object.assign || function(e) {
                for (var r, t = 1, n = arguments.length; t < n; t++) {
                    r = arguments[t];
                    for (var o in r) Object.prototype.hasOwnProperty.call(r, o) && (e[o] = r[o])
                }
                return e
            }, se.apply(this, arguments)
        },
        ue = function() {
            function e() {
                this.filterResources = r.memoize(n)
            }
            return e.prototype.transform = function(e, r, t, n) {
                if (r["class"].needsResourceData) return {
                    resourceStore: this.filterResources(t.resourceStore, n.opt("filterResourcesWithEvents"), t.eventStore, t.dateProfile.activeRange),
                    resourceEntityExpansions: t.resourceEntityExpansions
                }
            }, e
        }(),
        ie = function() {
            function e() {
                this.buildResourceEventUis = r.memoizeOutput(i, r.isObjectsSimilar), this.injectResourceEventUis = r.memoize(a)
            }
            return e.prototype.transform = function(e, r, t) {
                if (!r["class"].needsResourceData) return {
                    eventUiBases: this.injectResourceEventUis(e.eventUiBases, e.eventStore.defs, this.buildResourceEventUis(t.resourceStore))
                }
            }, e
        }(),
        ae = {
            id: String
        },
        ce = [],
        le = 0,
        de = 0,
        fe = {
            id: String,
            title: String,
            parentId: String,
            businessHours: null,
            children: null
        },
        pe = "_fc:",
        he = 0,
        ve = {
            resourceId: String,
            resourceIds: function(e) {
                return (e || []).map(function(e) {
                    return String(e)
                })
            },
            resourceEditable: Boolean
        },
        ge = function() {
            function e(e, r) {
                this._calendar = e, this._resource = r
            }
            return e.prototype.setProp = function(e, r) {
                this._calendar.dispatch({
                    type: "SET_RESOURCE_PROP",
                    resourceId: this._resource.id,
                    propName: e,
                    propValue: r
                })
            }, e.prototype.remove = function() {
                this._calendar.dispatch({
                    type: "REMOVE_RESOURCE",
                    resourceId: this._resource.id
                })
            }, e.prototype.getParent = function() {
                var r = this._calendar,
                    t = this._resource.parentId;
                return t ? new e(r, r.state.resourceSource[t]) : null
            }, e.prototype.getChildren = function() {
                var r = this._resource.id,
                    t = this._calendar,
                    n = t.state.resourceStore,
                    o = [];
                for (var s in n) n[s].parentId === r && o.push(new e(t, n[s]));
                return o
            }, e.prototype.getEvents = function() {
                var e = this._resource.id,
                    t = this._calendar,
                    n = t.state.eventStore,
                    o = n.defs,
                    s = n.instances,
                    u = [];
                for (var i in s) {
                    var a = s[i],
                        c = o[a.defId];
                    c.resourceIds.indexOf(e) !== -1 && u.push(new r.EventApi(t, c, a))
                }
                return u
            }, Object.defineProperty(e.prototype, "id", {
                get: function() {
                    return this._resource.id
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "title", {
                get: function() {
                    return this._resource.title
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventConstraint", {
                get: function() {
                    return this._resource.ui.constraints[0] || null
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventOverlap", {
                get: function() {
                    return this._resource.ui.overlap
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventAllow", {
                get: function() {
                    return this._resource.ui.allows[0] || null
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventBackgroundColor", {
                get: function() {
                    return this._resource.ui.backgroundColor
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventBorderColor", {
                get: function() {
                    return this._resource.ui.borderColor
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventTextColor", {
                get: function() {
                    return this._resource.ui.textColor
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "eventClassNames", {
                get: function() {
                    return this._resource.ui.classNames
                },
                enumerable: !0,
                configurable: !0
            }), Object.defineProperty(e.prototype, "extendedProps", {
                get: function() {
                    return this._resource.extendedProps
                },
                enumerable: !0,
                configurable: !0
            }), e
        }();
    r.Calendar.prototype.addResource = function(e, r) {
        var t;
        void 0 === r && (r = !0);
        var n, o;
        return e instanceof ge ? (o = e._resource, t = {}, t[o.id] = o, n = t) : (n = {}, o = m(e, "", n, this)), r && this.component.view.addScroll({
            forcedRowId: o.id
        }), this.dispatch({
            type: "ADD_RESOURCE",
            resourceHash: n
        }), new ge(this, o)
    }, r.Calendar.prototype.getResourceById = function(e) {
        if (e = String(e), this.state.resourceStore) {
            var r = this.state.resourceStore[e];
            if (r) return new ge(this, r)
        }
        return null
    }, r.Calendar.prototype.getResources = function() {
        var e = this.state.resourceStore,
            r = [];
        if (e)
            for (var t in e) r.push(new ge(this, e[t]));
        return r
    }, r.Calendar.prototype.getTopLevelResources = function() {
        var e = this.state.resourceStore,
            r = [];
        if (e)
            for (var t in e) e[t].parentId || r.push(new ge(this, e[t]));
        return r
    }, r.Calendar.prototype.rerenderResources = function() {
        this.dispatch({
            type: "RESET_RESOURCES"
        })
    }, r.Calendar.prototype.refetchResources = function() {
        this.dispatch({
            type: "REFETCH_RESOURCES"
        })
    };
    var Re = function(e) {
        function r() {
            return null !== e && e.apply(this, arguments) || this
        }
        return t(r, e), r.prototype.getKeyInfo = function(e) {
            return se({
                "": {}
            }, e.resourceStore)
        }, r.prototype.getKeysForDateSpan = function(e) {
            return [e.resourceId || ""]
        }, r.prototype.getKeysForEventDef = function(e) {
            var r = e.resourceIds;
            return r.length ? r : [""]
        }, r
    }(r.Splitter);
    r.EventApi.prototype.getResources = function() {
        var e = this._calendar;
        return this._def.resourceIds.map(function(r) {
            return e.getResourceById(r)
        })
    }, r.EventApi.prototype.setResources = function(e) {
        for (var r = [], t = 0, n = e; t < n.length; t++) {
            var o = n[t],
                s = null;
            "string" == typeof o ? s = o : "number" == typeof o ? s = String(o) : o instanceof ge ? s = o.id : console.warn("unknown resource type: " + o), s && r.push(s)
        }
        this.mutate({
            standardProps: {
                resourceIds: r
            }
        })
    };
    var ye = "2019-04-03",
        Ee = 372,
        me = "http://fullcalendar.io/scheduler/license/",
        Se = ["GPL-My-Project-Is-Open-Source", "CC-Attribution-NonCommercial-NoDerivatives"],
        Ie = {
            position: "absolute",
            "z-index": 99999,
            bottom: "1px",
            left: "1px",
            background: "#eee",
            "border-color": "#ddd",
            "border-style": "solid",
            "border-width": "1px 1px 0 0",
            padding: "2px 4px",
            "font-size": "12px",
            "border-top-right-radius": "3px"
        },
        be = {
            resources: G
        };
    l({
        ignoreRange: !0,
        parseMeta: function(e) {
            return Array.isArray(e) ? e : Array.isArray(e.resources) ? e.resources : null
        },
        fetch: function(e, r) {
            r({
                rawResources: e.resourceSource.meta
            })
        }
    }), l({
        parseMeta: function(e) {
            return "function" == typeof e ? e : "function" == typeof e.resources ? e.resources : null
        },
        fetch: function(e, t, n) {
            var o = e.calendar.dateEnv,
                s = e.resourceSource.meta,
                u = {};
            e.range && (u = {
                start: o.toDate(e.range.start),
                end: o.toDate(e.range.end),
                startStr: o.formatIso(e.range.start),
                endStr: o.formatIso(e.range.end),
                timeZone: o.timeZone
            }), r.unpromisify(s.bind(null, u), function(e) {
                t({
                    rawResources: e
                })
            }, n)
        }
    }), l({
        parseMeta: function(e) {
            if ("string" == typeof e) e = {
                url: e
            };
            else if (!e || "object" != typeof e || !e.url) return null;
            return {
                url: e.url,
                method: (e.method || "GET").toUpperCase(),
                extraParams: e.extraParams
            }
        },
        fetch: function(e, t, n) {
            var o = e.resourceSource.meta,
                s = L(o, e.range, e.calendar);
            r.requestJson("GET", o.url, s, function(e, r) {
                t({
                    rawResources: e,
                    xhr: r
                })
            }, function(e, r) {
                n({
                    message: e,
                    xhr: r
                })
            })
        }
    });
    var Ce = function(e) {
            function n(t, n) {
                var o = e.call(this, t) || this;
                return o.datesAboveResources = o.opt("datesAboveResources"), o.resourceTextFunc = Z(o.opt("resourceText"), o.calendar), n.innerHTML = "", n.appendChild(o.el = r.htmlToElement('<div class="fc-row ' + o.theme.getClass("headerRow") + '"><table class="' + o.theme.getClass("tableGrid") + '"><thead></thead></table></div>')), o.thead = o.el.querySelector("thead"), o
            }
            return t(n, e), n.prototype.destroy = function() {
                r.removeElement(this.el)
            }, n.prototype.render = function(e) {
                var t;
                this.dateFormat = r.createFormatter(this.opt("columnHeaderFormat") || r.computeFallbackHeaderFormat(e.datesRepDistinctDays, e.dates.length)), t = 1 === e.dates.length ? this.renderResourceRow(e.resources) : this.datesAboveResources ? this.renderDayAndResourceRows(e.dates, e.resources) : this.renderResourceAndDayRows(e.resources, e.dates), this.thead.innerHTML = t, this.processResourceEls(e.resources)
            }, n.prototype.renderResourceRow = function(e) {
                var r = this,
                    t = e.map(function(e) {
                        return r.renderResourceCell(e, 1)
                    });
                return this.buildTr(t)
            }, n.prototype.renderDayAndResourceRows = function(e, r) {
                for (var t = [], n = [], o = 0, s = e; o < s.length; o++) {
                    var u = s[o];
                    t.push(this.renderDateCell(u, r.length));
                    for (var i = 0, a = r; i < a.length; i++) {
                        var c = a[i];
                        n.push(this.renderResourceCell(c, 1, u))
                    }
                }
                return this.buildTr(t) + this.buildTr(n)
            }, n.prototype.renderResourceAndDayRows = function(e, r) {
                for (var t = [], n = [], o = 0, s = e; o < s.length; o++) {
                    var u = s[o];
                    t.push(this.renderResourceCell(u, r.length));
                    for (var i = 0, a = r; i < a.length; i++) {
                        var c = a[i];
                        n.push(this.renderDateCell(c, 1, u))
                    }
                }
                return this.buildTr(t) + this.buildTr(n)
            }, n.prototype.renderResourceCell = function(e, t, n) {
                var o = this.dateEnv;
                return '<th class="fc-resource-cell" data-resource-id="' + e.id + '"' + (n ? ' data-date="' + o.formatIso(n, {
                    omitTime: !0
                }) + '"' : "") + (t > 1 ? ' colspan="' + t + '"' : "") + ">" + r.htmlEscape(this.resourceTextFunc(e)) + "</th>"
            }, n.prototype.renderDateCell = function(e, t, n) {
                var o = this.props;
                return r.renderDateCell(e, o.dateProfile, o.datesRepDistinctDays, o.dates.length * o.resources.length, this.dateFormat, this.context, t, n ? 'data-resource-id="' + n.id + '"' : "")
            }, n.prototype.buildTr = function(e) {
                return e.length || (e = ["<td>&nbsp;</td>"]), this.props.renderIntroHtml && (e = [this.props.renderIntroHtml()].concat(e)), this.isRtl && e.reverse(), "<tr>" + e.join("") + "</tr>"
            }, n.prototype.processResourceEls = function(e) {
                var t = this,
                    n = this.view;
                r.findElements(this.thead, ".fc-resource-cell").forEach(function(r, o) {
                    o %= e.length, t.isRtl && (o = e.length - 1 - o);
                    var s = e[o];
                    n.publiclyTrigger("resourceRender", [{
                        resource: new ge(t.calendar, s),
                        el: r,
                        view: n
                    }])
                })
            }, n
        }(r.Component),
        _e = function() {
            function e(e, r) {
                this.dayTable = e, this.resources = r, this.resourceIndex = new Pe(r), this.rowCnt = e.rowCnt, this.colCnt = e.colCnt * r.length, this.cells = this.buildCells()
            }
            return e.prototype.buildCells = function() {
                for (var e = this, r = e.rowCnt, t = e.dayTable, n = e.resources, o = [], s = 0; s < r; s++) {
                    for (var u = [], i = 0; i < t.colCnt; i++)
                        for (var a = 0; a < n.length; a++) {
                            var c = n[a],
                                l = 'data-resource-id="' + c.id + '"';
                            u[this.computeCol(i, a)] = {
                                date: t.cells[s][i].date,
                                resource: c,
                                htmlAttrs: l
                            }
                        }
                    o.push(u)
                }
                return o
            }, e
        }(),
        we = function(e) {
            function r() {
                return null !== e && e.apply(this, arguments) || this
            }
            return t(r, e), r.prototype.computeCol = function(e, r) {
                return r * this.dayTable.colCnt + e
            }, r.prototype.computeColRanges = function(e, r, t) {
                return [{
                    firstCol: this.computeCol(e, t),
                    lastCol: this.computeCol(r, t),
                    isStart: !0,
                    isEnd: !0
                }]
            }, r
        }(_e),
        Oe = function(e) {
            function r() {
                return null !== e && e.apply(this, arguments) || this
            }
            return t(r, e), r.prototype.computeCol = function(e, r) {
                return e * this.resources.length + r
            }, r.prototype.computeColRanges = function(e, r, t) {
                for (var n = [], o = e; o <= r; o++) {
                    var s = this.computeCol(o, t);
                    n.push({
                        firstCol: s,
                        lastCol: s,
                        isStart: o === e,
                        isEnd: o === r
                    })
                }
                return n
            }, r
        }(_e),
        Pe = function() {
            function e(e) {
                for (var r = {}, t = [], n = 0; n < e.length; n++) {
                    var o = e[n].id;
                    t.push(o), r[o] = n
                }
                this.ids = t, this.indicesById = r, this.length = e.length
            }
            return e
        }(),
        Te = function(e) {
            function n() {
                return null !== e && e.apply(this, arguments) || this
            }
            return t(n, e), n.prototype.getKeyInfo = function(e) {
                var t = e.resourceDayTable,
                    n = r.mapHash(t.resourceIndex.indicesById, function(e) {
                        return t.resources[e]
                    });
                return n[""] = {}, n
            }, n.prototype.getKeysForDateSpan = function(e) {
                return [e.resourceId || ""]
            }, n.prototype.getKeysForEventDef = function(e) {
                var r = e.resourceIds;
                return r.length ? r : [""]
            }, n
        }(r.Splitter),
        xe = [],
        De = function() {
            function e() {
                this.joinDateSelection = r.memoize(this.joinSegs), this.joinBusinessHours = r.memoize(this.joinSegs), this.joinFgEvents = r.memoize(this.joinSegs), this.joinBgEvents = r.memoize(this.joinSegs), this.joinEventDrags = r.memoize(this.joinInteractions), this.joinEventResizes = r.memoize(this.joinInteractions)
            }
            return e.prototype.joinProps = function(e, r) {
                for (var t = [], n = [], o = [], s = [], u = [], i = [], a = "", c = r.resourceIndex.ids.concat([""]), l = 0, d = c; l < d.length; l++) {
                    var f = d[l],
                        p = e[f];
                    t.push(p.dateSelectionSegs), n.push(f ? p.businessHourSegs : xe), o.push(f ? p.fgEventSegs : xe), s.push(p.bgEventSegs), u.push(p.eventDrag), i.push(p.eventResize), a = a || p.eventSelection
                }
                return {
                    dateSelectionSegs: this.joinDateSelection.apply(this, [r].concat(t)),
                    businessHourSegs: this.joinBusinessHours.apply(this, [r].concat(n)),
                    fgEventSegs: this.joinFgEvents.apply(this, [r].concat(o)),
                    bgEventSegs: this.joinBgEvents.apply(this, [r].concat(s)),
                    eventDrag: this.joinEventDrags.apply(this, [r].concat(u)),
                    eventResize: this.joinEventResizes.apply(this, [r].concat(i)),
                    eventSelection: a
                }
            }, e.prototype.joinSegs = function(e) {
                for (var r = [], t = 1; t < arguments.length; t++) r[t - 1] = arguments[t];
                for (var n = e.resources.length, o = [], s = 0; s < n; s++) {
                    for (var u = 0, i = r[s]; u < i.length; u++) {
                        var a = i[u];
                        o.push.apply(o, this.transformSeg(a, e, s))
                    }
                    for (var c = 0, l = r[n]; c < l.length; c++) {
                        var a = l[c];
                        o.push.apply(o, this.transformSeg(a, e, s))
                    }
                }
                return o
            }, e.prototype.expandSegs = function(e, r) {
                for (var t = e.resources.length, n = [], o = 0; o < t; o++)
                    for (var s = 0, u = r; s < u.length; s++) {
                        var i = u[s];
                        n.push.apply(n, this.transformSeg(i, e, o))
                    }
                return n
            }, e.prototype.joinInteractions = function(e) {
                for (var r = [], t = 1; t < arguments.length; t++) r[t - 1] = arguments[t];
                for (var n = e.resources.length, o = {}, s = [], u = !1, i = null, a = 0; a < n; a++) {
                    var c = r[a];
                    if (c) {
                        for (var l = 0, d = c.segs; l < d.length; l++) {
                            var f = d[l];
                            s.push.apply(s, this.transformSeg(f, e, a))
                        }
                        se(o, c.affectedInstances), u = u || c.isEvent, i = i || c.sourceSeg
                    }
                    if (r[n])
                        for (var p = 0, h = r[n].segs; p < h.length; p++) {
                            var f = h[p];
                            s.push.apply(s, this.transformSeg(f, e, a))
                        }
                }
                return {
                    affectedInstances: o,
                    segs: s,
                    isEvent: u,
                    sourceSeg: i
                }
            }, e
        }(),
        je = r.createPlugin({
            reducers: [P],
            eventDefParsers: [T],
            eventDragMutationMassagers: [x],
            eventDefMutationAppliers: [D],
            dateSelectionTransformers: [U],
            datePointTransforms: [A],
            dateSpanTransforms: [H],
            viewPropsTransformers: [ue, ie],
            isPropsValid: B,
            externalDefTransforms: [V],
            eventResizeJoinTransforms: [N],
            viewContainerModifiers: [k],
            eventDropTransformers: [F],
            optionChangeHandlers: be
        });
    e.AbstractResourceDayTable = _e, e.DayResourceTable = Oe, e.ResourceApi = ge, e.ResourceDayHeader = Ce, e.ResourceDayTable = we, e.ResourceSplitter = Re, e.VResourceJoiner = De, e.VResourceSplitter = Te, e.buildResourceFields = te, e.buildResourceTextFunc = Z, e.buildRowNodes = W, e.computeResourceEditable = j, e["default"] = je, e.flattenResources = J, e.isGroupsEqual = ne, Object.defineProperty(e, "__esModule", {
        value: !0
    })
});
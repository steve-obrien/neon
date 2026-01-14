"use strict";

Vue.component('neon-modal-resizer', {
  template: "<div :class=\"className\"></div>",
  props: {
    minHeight: {
      type: Number,
      default: 0
    },
    minWidth: {
      type: Number,
      default: 0
    }
  },
  data: function data() {
    return {
      clicked: false,
      size: {}
    };
  },
  mounted: function mounted() {
    this.$el.addEventListener('mousedown', this.start, false);
  },
  computed: {
    className: function className() {
      return {
        'neonModalResizer': true,
        clicked: this.clicked
      };
    }
  },
  methods: {
    start: function start(event) {
      this.clicked = true;
      window.addEventListener('mousemove', this.mousemove, false);
      window.addEventListener('mouseup', this.stop, false);
      event.stopPropagation();
      event.preventDefault();
    },
    stop: function stop() {
      this.clicked = false;
      window.removeEventListener('mousemove', this.mousemove, false);
      window.removeEventListener('mouseup', this.stop, false);
      this.$emit('resize-stop', {
        element: this.$el.parentElement,
        size: this.size
      });
    },
    mousemove: function mousemove(event) {
      this.resize(event);
    },
    inRange: function inRange(min, max, val) {
      return val < min ? min : val > max ? max : val;
    },
    resize: function resize(event) {
      var el = this.$el.parentElement;

      if (el) {
        var width = event.clientX - el.offsetLeft;
        var height = event.clientY - el.offsetTop;
        width = this.inRange(this.minWidth, window.innerWidth, width);
        height = this.inRange(this.minHeight, window.innerHeight, height);
        this.size = {
          width: width,
          height: height
        };
        el.style.width = width + 'px';
        el.style.height = height + 'px';
        this.$emit('resize', {
          element: el,
          size: this.size
        });
      }
    }
  }
});
AFRAME.registerComponent('ar-event-info', {
    init: function () {
        this.el.addEventListener('click', function (evt) {
            alert('Event information: ' + this.getAttribute('data-info'));
        });
    }
});

function initAR(modelUrl, eventInfo) {
    const scene = document.querySelector('a-scene');
    if (!scene) {
        const newScene = document.createElement('a-scene');
        newScene.setAttribute('embedded', '');
        newScene.setAttribute('arjs', 'sourceType: webcam; debugUIEnabled: false;');
        
        const marker = document.createElement('a-marker');
        marker.setAttribute('preset', 'hiro');
        
        const model = document.createElement('a-entity');
        model.setAttribute('gltf-model', modelUrl);
        model.setAttribute('scale', '0.1 0.1 0.1');
        model.setAttribute('ar-event-info', '');
        model.setAttribute('data-info', eventInfo);
        
        marker.appendChild(model);
        newScene.appendChild(marker);
        
        const camera = document.createElement('a-entity');
        camera.setAttribute('camera', '');
        newScene.appendChild(camera);
        
        document.body.appendChild(newScene);
    }
}

jQuery(document).ready(function($) {
    $('.dc-event-ar-view').on('click', function(e) {
        e.preventDefault();
        var modelUrl = $(this).data('model');
        var eventInfo = $(this).data('info');
        initAR(modelUrl, eventInfo);
    });
});
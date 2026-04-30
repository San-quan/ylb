// 二维码生成器 JavaScript

let qrcode = null;
let logoDataUrl = null;

// 颜色选择器实时更新
document.getElementById('qrColorDark').addEventListener('input', function() {
    document.getElementById('colorDarkValue').textContent = this.value;
});

document.getElementById('qrColorLight').addEventListener('input', function() {
    document.getElementById('colorLightValue').textContent = this.value;
});

// 切换Logo上传区域
function toggleLogoUpload() {
    const checkbox = document.getElementById('qrLogo');
    const section = document.getElementById('logoUploadSection');
    section.style.display = checkbox.checked ? 'block' : 'none';
}

// 预览Logo
function previewLogo() {
    const fileInput = document.getElementById('qrLogoImg');
    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            logoDataUrl = e.target.result;
            document.getElementById('logoImgPreview').src = logoDataUrl;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// 生成二维码
function generateQRCode() {
    const content = document.getElementById('qrContent').value.trim();
    if (!content) {
        alert('请输入内容');
        return;
    }
    
    // 获取参数
    const options = {
        text: content,
        width: parseInt(document.getElementById('qrSize').value),
        height: parseInt(document.getElementById('qrSize').value),
        colorDark: document.getElementById('qrColorDark').value,
        colorLight: document.getElementById('qrColorLight').value,
        margin: parseInt(document.getElementById('qrMargin').value),
        correctLevel: document.getElementById('qrCorrectLevel').value,
        dotStyle: document.getElementById('qrDotStyle').value,
        cornerSquareOptions: {
            type: document.getElementById('qrCornerStyle').value
        },
        cornerDotOptions: {
            type: document.getElementById('qrCornerStyle').value
        }
    };
    
    // 添加Logo
    if (document.getElementById('qrLogo').checked && logoDataUrl) {
        options.image = logoDataUrl;
        options.imageSize = 0.4;
        options.imageExemptOptions = {
            cornerSquare: true,
            cornerDot: true
        };
    }
    
    // 清空之前的二维码
    const preview = document.getElementById('qrPreview');
    preview.innerHTML = '';
    
    // 生成新的二维码
    qrcode = new EasyQRCode(document.getElementById('qrPreview'), options);
    
    // 启用下载按钮
    document.getElementById('downloadBtn').disabled = false;
    
    // 自动下载
    if (document.getElementById('downloadAuto').checked) {
        setTimeout(downloadQRCode, 500);
    }
}

// 下载二维码
function downloadQRCode() {
    const canvas = document.querySelector('#qrPreview canvas');
    if (canvas) {
        const link = document.createElement('a');
        link.download = 'qrcode_' + Date.now() + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    } else {
        alert('请先生成二维码');
    }
}
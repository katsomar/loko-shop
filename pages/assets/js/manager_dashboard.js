document.addEventListener('DOMContentLoaded', function() {
    // Welcome balls animation
    (function() {
        const banner = document.querySelector('.welcome-banner');
        const ballsContainer = document.querySelector('.welcome-balls');
        if (!banner || !ballsContainer) return;

        function getColors() {
            if (document.body.classList.contains('dark-mode')) {
                return ['#ffd200', '#1abc9c', '#56ccf2', '#23243a', '#fff'];
            } else {
                return ['#1abc9c', '#56ccf2', '#ffd200', '#3498db', '#fff'];
            }
        }

        ballsContainer.innerHTML = '';
        ballsContainer.style.position = 'absolute';
        ballsContainer.style.top = 0;
        ballsContainer.style.left = 0;
        ballsContainer.style.width = '100%';
        ballsContainer.style.height = '100%';
        ballsContainer.style.zIndex = 1;
        ballsContainer.style.pointerEvents = 'none';

        const balls = [];
        const colors = getColors();
        const numBalls = 7;
        for (let i = 0; i < numBalls; i++) {
            const ball = document.createElement('div');
            ball.className = 'welcome-ball';
            ball.style.position = 'absolute';
            ball.style.borderRadius = '50%';
            ball.style.opacity = '0.18';
            ball.style.background = colors[i % colors.length];
            ball.style.width = ball.style.height = (32 + Math.random() * 32) + 'px';
            ball.style.top = (10 + Math.random() * 60) + '%';
            ball.style.left = (5 + Math.random() * 85) + '%';
            ballsContainer.appendChild(ball);
            balls.push({
                el: ball,
                x: parseFloat(ball.style.left),
                y: parseFloat(ball.style.top),
                r: Math.random() * 0.5 + 0.2,
                dx: (Math.random() - 0.5) * 0.2,
                dy: (Math.random() - 0.5) * 0.2
            });
        }

        function animateBalls() {
            balls.forEach(ball => {
                ball.x += ball.dx;
                ball.y += ball.dy;
                if (ball.x < 0 || ball.x > 95) ball.dx *= -1;
                if (ball.y < 5 || ball.y > 80) ball.dy *= -1;
                ball.el.style.left = ball.x + '%';
                ball.el.style.top = ball.y + '%';
            });
            requestAnimationFrame(animateBalls);
        }
        animateBalls();

        window.addEventListener('storage', () => {
            const newColors = getColors();
            balls.forEach((ball, i) => {
                ball.el.style.background = newColors[i % newColors.length];
            });
        });
        document.getElementById('themeToggle')?.addEventListener('change', () => {
            const newColors = getColors();
            balls.forEach((ball, i) => {
                ball.el.style.background = newColors[i % newColors.length];
            });
        });
    })();
});

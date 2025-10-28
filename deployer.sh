# Tạo user chuyên deploy (an toàn hơn dùng root)
sudo adduser deploy
sudo usermod -aG www-data deploy  # nếu web chạy user/group này
sudo -u deploy mkdir -p /var/www/app/{current,releases,tmp}

# Bật SSH key cho user deploy
sudo -u deploy mkdir -p /home/deploy/.ssh && chmod 700 /home/deploy/.ssh
sudo -u deploy nano /home/deploy/.ssh/authorized_keys   # dán public key vào
sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys

# (khuyến nghị) Cho phép deploy user restart service cần thiết qua sudo (không yêu cầu mật khẩu)
# /etc/sudoers.d/deploy
deploy ALL=(ALL) NOPASSWD:/usr/bin/systemctl reload php*-fpm, /usr/bin/systemctl restart nginx
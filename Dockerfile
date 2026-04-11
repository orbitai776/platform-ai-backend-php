# Dùng bản PHP CLI trên nền Alpine Linux (Cực kỳ nhẹ, chỉ khoảng ~25-30MB)
FROM php:8.2-cli-alpine

# Thêm thư viện curl cho hệ điều hành (thường bản php alpine đã có sẵn, nhưng cứ thêm cho chắc, --no-cache để không rác image)
RUN apk add --no-cache curl

# Chuyển thư mục làm việc vào /app
WORKDIR /app

# Copy file index.php từ máy tính vào trong image
COPY index.php /app/

# Mở port 8000
EXPOSE 8000

# Khởi chạy server tích hợp sẵn của PHP. 
# Chạy 1 process duy nhất -> Tối ưu RAM tuyệt đối.
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]
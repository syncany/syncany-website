select *
from app
where (dist, type, os, arch, id) in (
    select dist, type, os, arch, max(id)
    from app
    where {where}
    group by dist, type, os, arch
)